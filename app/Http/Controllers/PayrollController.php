<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Employee;
use App\Models\SalaryStructure;
use App\Models\Deduction;
use App\Models\Bonus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf; 
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Facades\Log;

    
class PayrollController extends Controller
{
    public function index(Request $request)
    {
        
        $currentPayroll = Payroll::orderBy('end_date', 'desc')->first();
        
        
        if (!$currentPayroll) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        } else {
            $startDate = Carbon::parse($currentPayroll->start_date);
            $endDate = Carbon::parse($currentPayroll->end_date);
        }
        
        
        $selectedStartDate = $request->query('start_date', $startDate->format('Y-m-d'));
        $selectedEndDate = $request->query('end_date', $endDate->format('Y-m-d'));
        
        
        $payroll = Payroll::where('start_date', $selectedStartDate)
            ->where('end_date', $selectedEndDate)
            ->first();
            
        
        if (!$payroll) {
            $payroll = $this->getOrCreatePayroll($selectedStartDate, $selectedEndDate);
        }
        
        
        $perPage = $request->query('per_page', 10);
        $payrollItems = PayrollItem::where('payroll_id', $payroll->id)
            ->with(['employee', 'employee.user', 'deductions', 'bonuses'])
            ->paginate($perPage)
            ->through(function ($item) {
                
                $overtimeAmount = $item->bonuses->where('bonus_type', 'overtime')->sum('amount');
                
                
                $cashAdvance = $item->deductions->where('deduction_type', 'advance')->sum('amount');
                
                
                $profilePicture = $item->employee->profile_picture 
                    ? (str_starts_with($item->employee->profile_picture, 'http') 
                        ? $item->employee->profile_picture 
                        : "/storage/{$item->employee->profile_picture}") 
                    : '/placeholder.svg?height=40&width=40';

                return [
                    'id' => $item->id,
                    'employee' => [
                        'id' => $item->employee->id,
                        'name' => $item->employee->full_name,
                        'employee_id' => $item->employee->employee_id,
                        'profile_picture' => $profilePicture,
                    ],
                    'gross' => $item->gross_salary,
                    'deductions' => $item->total_deductions,
                    'cash_advance' => $cashAdvance,
                    'overtime' => $overtimeAmount,
                    'net_pay' => $item->net_salary,
                ];
            });
            
        
        $payrollPeriods = Payroll::orderBy('end_date', 'desc')
            ->get()
            ->map(function ($period) {
                return [
                    'id' => $period->id,
                    'start_date' => $period->start_date,
                    'end_date' => $period->end_date,
                    'label' => Carbon::parse($period->start_date)->format('M d, Y') . ' - ' . 
                              Carbon::parse($period->end_date)->format('M d, Y'),
                ];
            });
            
        
        $payrollData = [
            'id' => $payroll->id,
            'status' => $payroll->status ?? 'processing', 
            'payment_date' => $payroll->payment_date ?? Carbon::parse($selectedEndDate)->addDays(5)->format('Y-m-d'),
            'reference' => $payroll->payroll_reference ?? ('PAY-' . Carbon::parse($selectedStartDate)->format('Ym')),
        ];
        
        return Inertia::render('Payroll/index', [
            'payrollItems' => $payrollItems,
            'payrollPeriods' => $payrollPeriods,
            'currentPeriod' => [
                'start_date' => $selectedStartDate,
                'end_date' => $selectedEndDate,
                'formatted' => Carbon::parse($selectedStartDate)->format('M d, Y') . ' - ' . 
                              Carbon::parse($selectedEndDate)->format('M d, Y'),
            ],
            'payroll' => $payrollData, 
        ]);
    }
    
    private function getOrCreatePayroll($startDate, $endDate)
    {
        
        $payroll = Payroll::where('start_date', $startDate)
            ->where('end_date', $endDate)
            ->first();
            
        if ($payroll) {
            return $payroll;
        }
        
        
        $payroll = Payroll::create([
            'payroll_reference' => 'PAY-' . Carbon::parse($startDate)->format('Ym'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'payment_date' => Carbon::parse($endDate)->addDays(5)->format('Y-m-d'),
            'status' => 'processing',
            'created_by' => Auth::id(),
        ]);
        
        
        $this->generatePayrollItems($payroll);
        
        return $payroll;
    }
    
    private function generatePayrollItems($payroll)
    {
        
        $employees = Employee::whereNull('termination_date')
            ->orWhere('termination_date', '>', $payroll->end_date)
            ->get();
            
        foreach ($employees as $employee) {
            
            if (strtotime($employee->hire_date) > strtotime($payroll->end_date)) {
                continue;
            }
            
            
            $salaryStructure = SalaryStructure::where('employee_id', $employee->id)
                ->where('is_current', true)
                ->first();
                
            if (!$salaryStructure) {
                continue; 
            }
            
            
            $totalAllowances = $salaryStructure->housing_allowance +
                $salaryStructure->transport_allowance +
                $salaryStructure->meal_allowance +
                $salaryStructure->medical_allowance +
                $salaryStructure->other_allowances;
                
            
            $grossSalary = $salaryStructure->basic_salary + $totalAllowances;
            
            
            $payrollItem = PayrollItem::create([
                'payroll_id' => $payroll->id,
                'employee_id' => $employee->id,
                'basic_salary' => $salaryStructure->basic_salary,
                'total_allowances' => $totalAllowances,
                'total_deductions' => 0, 
                'total_bonuses' => 0, 
                'gross_salary' => $grossSalary,
                'net_salary' => $grossSalary, 
                'working_days' => $this->calculateWorkingDays($payroll->start_date, $payroll->end_date, $employee->id),
            ]);
            
            
            $this->generateStandardDeductions($payrollItem);
            
            
            $this->generateStandardBonuses($payrollItem);
            
            
            $this->updatePayrollItemTotals($payrollItem);
        }
    }
    
    private function calculateWorkingDays($startDate, $endDate, $employeeId)
    {
        
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $workingDays = 0;
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            
            if ($date->isWeekend()) {
                continue;
            }
            
            
            $onLeave = \App\Models\LeaveRequest::where('employee_id', $employeeId)
                ->where('status', 'approved')
                ->where('start_date', '<=', $date->format('Y-m-d'))
                ->where('end_date', '>=', $date->format('Y-m-d'))
                ->exists();
                
            if (!$onLeave) {
                $workingDays++;
            }
        }
        
        return $workingDays;
    }
    
    private function generateStandardDeductions($payrollItem)
    {
        
        Deduction::create([
            'payroll_item_id' => $payrollItem->id,
            'deduction_type' => 'tax',
            'description' => 'Income Tax',
            'amount' => $payrollItem->gross_salary * 0.1, 
        ]);
        
        
        Deduction::create([
            'payroll_item_id' => $payrollItem->id,
            'deduction_type' => 'pension',
            'description' => 'Pension Contribution',
            'amount' => $payrollItem->basic_salary * 0.05, 
        ]);
        
        
        Deduction::create([
            'payroll_item_id' => $payrollItem->id,
            'deduction_type' => 'health_insurance',
            'description' => 'Health Insurance',
            'amount' => $payrollItem->basic_salary * 0.03, 
        ]);
    }
    
    private function generateStandardBonuses($payrollItem)
    {
        
        $employee = Employee::find($payrollItem->employee_id);
        
        
        $avgKpiScore = \App\Models\KpiRecord::whereHas('employeeKpi', function($query) use ($employee) {
            $query->where('employee_id', $employee->id);
        })
        ->whereBetween('record_date', [$payrollItem->payroll->start_date, $payrollItem->payroll->end_date])
        ->avg('achievement_percentage');
        
        
        if ($avgKpiScore > 90) {
            Bonus::create([
                'payroll_item_id' => $payrollItem->id,
                'bonus_type' => 'performance',
                'description' => 'Performance Bonus',
                'amount' => $payrollItem->basic_salary * 0.05, 
            ]);
        }
    }
    
    private function updatePayrollItemTotals($payrollItem)
    {
        
        $totalDeductions = Deduction::where('payroll_item_id', $payrollItem->id)->sum('amount');
        
        
        $totalBonuses = Bonus::where('payroll_item_id', $payrollItem->id)->sum('amount');
        
        
        $payrollItem->total_deductions = $totalDeductions;
        $payrollItem->total_bonuses = $totalBonuses;
        $payrollItem->net_salary = $payrollItem->gross_salary - $totalDeductions + $totalBonuses;
        $payrollItem->save();
    }
    
    public function generatePayslip($id)
    {
        $payrollItem = PayrollItem::with([
            'employee', 
            'employee.department', 
            'employee.position',
            'payroll',
            'deductions',
            'bonuses'
        ])->findOrFail($id);
        
        
        $payslipData = [
            'id' => $payrollItem->id,
            'employee' => [
                'id' => $payrollItem->employee->id,
                'name' => $payrollItem->employee->full_name,
                'employee_id' => $payrollItem->employee->employee_id,
                'department' => $payrollItem->employee->department ? $payrollItem->employee->department->name : 'N/A',
                'position' => $payrollItem->employee->position ? $payrollItem->employee->position->title : 'N/A',
                'join_date' => $payrollItem->employee->hire_date,
                'bank_name' => $payrollItem->employee->bank_name ?? 'N/A',
                'bank_account' => $payrollItem->employee->bank_account_number ?? 'N/A',
            ],
            'payroll_period' => [
                'start_date' => $payrollItem->payroll->start_date,
                'end_date' => $payrollItem->payroll->end_date,
                'payment_date' => $payrollItem->payroll->payment_date,
                'formatted' => Carbon::parse($payrollItem->payroll->start_date)->format('M d, Y') . ' - ' . 
                              Carbon::parse($payrollItem->payroll->end_date)->format('M d, Y'),
            ],
            'earnings' => [
                'basic_salary' => $payrollItem->basic_salary,
                'allowances' => $payrollItem->total_allowances,
                'bonuses' => $payrollItem->bonuses->map(function($bonus) {
                    return [
                        'type' => $bonus->bonus_type,
                        'description' => $bonus->description,
                        'amount' => $bonus->amount,
                    ];
                }),
                'total_earnings' => $payrollItem->gross_salary + $payrollItem->total_bonuses,
            ],
            'deductions' => $payrollItem->deductions->map(function($deduction) {
                return [
                    'type' => $deduction->deduction_type,
                    'description' => $deduction->description,
                    'amount' => $deduction->amount,
                ];
            }),
            'total_deductions' => $payrollItem->total_deductions,
            'net_pay' => $payrollItem->net_salary,
            'working_days' => $payrollItem->working_days ?? 0,
        ];
        
        return Inertia::render('Payroll/Payslip', [
            'payrollItem' => $payslipData
        ]);
    }
    
    public function processPayroll($id)
    {
        $payroll = Payroll::findOrFail($id);
        
        
        $payroll->status = 'approved';
        $payroll->approved_by = Auth::id();
        $payroll->approved_at = now();
        $payroll->save();
        
        return redirect()->back()->with('success', 'Payroll has been processed successfully.');
    }
    
    public function releasePayroll($id)
    {
        $payroll = Payroll::findOrFail($id);
        
        
        if ($payroll->status !== 'approved') {
            return redirect()->back()->with('error', 'Payroll must be approved before it can be released.');
        }
        
        
        $payroll->status = 'paid';
        $payroll->save();
        
        return redirect()->back()->with('success', 'Payroll has been released successfully.');
    }

    public function downloadPayslip($id)
    {
        $payrollItem = Payroll::with(['employee', 'payrollPeriod'])->findOrFail($id);

        
        $pdf = Pdf::loadView('payroll.payslip_pdf', ['payrollItem' => $payrollItem]);

        
        return $pdf->download("Payslip_{$payrollItem->employee->employee_id}.pdf");
    }

    public function payslip($id)
    {
        $payrollItem = PayrollItem::with([
            'employee', 
            'employee.department', 
            'employee.position',
            'payroll',
            'deductions',
            'bonuses'
        ])->findOrFail($id);

        
        $payslipData = [
            'id' => $payrollItem->id,
            'employee' => [
                'id' => $payrollItem->employee->id,
                'name' => $payrollItem->employee->full_name,
                'employee_id' => $payrollItem->employee->employee_id,
                'department' => $payrollItem->employee->department ? $payrollItem->employee->department->name : 'N/A',
                'position' => $payrollItem->employee->position ? $payrollItem->employee->position->title : 'N/A',
                'join_date' => $payrollItem->employee->hire_date,
                'bank_name' => $payrollItem->employee->bank_name ?? 'N/A',
                'bank_account' => $payrollItem->employee->bank_account_number ?? 'N/A',
            ],
            'payroll_period' => [
                'start_date' => $payrollItem->payroll->start_date,
                'end_date' => $payrollItem->payroll->end_date,
                'payment_date' => $payrollItem->payroll->payment_date,
                'formatted' => Carbon::parse($payrollItem->payroll->start_date)->format('M d, Y') . ' - ' . 
                              Carbon::parse($payrollItem->payroll->end_date)->format('M d, Y'),
            ],
            'earnings' => [
                'basic_salary' => $payrollItem->basic_salary,
                'allowances' => $payrollItem->total_allowances,
                'bonuses' => $payrollItem->bonuses->map(function($bonus) {
                    return [
                        'type' => $bonus->bonus_type,
                        'description' => $bonus->description,
                        'amount' => $bonus->amount,
                    ];
                }),
                'total_earnings' => $payrollItem->gross_salary + $payrollItem->total_bonuses,
            ],
            'deductions' => $payrollItem->deductions->map(function($deduction) {
                return [
                    'type' => $deduction->deduction_type,
                    'description' => $deduction->description,
                    'amount' => $deduction->amount,
                ];
            }),
            'total_deductions' => $payrollItem->total_deductions,
            'net_pay' => $payrollItem->net_salary,
            'working_days' => $payrollItem->working_days ?? 0,
        ];

        return Inertia::render('Payroll/Payslip', [
            'payrollItem' => $payslipData
        ]);
    }

    public function downloadAllPayslips(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            
            $payroll = Payroll::where('start_date', $startDate)
                ->where('end_date', $endDate)
                ->firstOrFail();

            
            $payrollItems = PayrollItem::where('payroll_id', $payroll->id)->with('employee')->get();

            if ($payrollItems->isEmpty()) {
                return back()->with('error', 'No payroll items found for the selected period.');
            }

            
            $zipFileName = "Payslips_{$startDate}_to_{$endDate}.zip";
            $zip = new ZipArchive;

            $zipPath = storage_path("app/{$zipFileName}");
            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                foreach ($payrollItems as $item) {
                    
                    $pdf = Pdf::loadView('payroll.payslip_pdf', ['payrollItem' => $item]);
                    $zip->addFromString("Payslip_{$item->employee->employee_id}.pdf", $pdf->output());
                }
                $zip->close();
            } else {
                throw new \Exception('Failed to create ZIP file.');
            }

            
            session()->flash('success', 'Payslips downloaded successfully.');

            
            return response()->download($zipPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            
            Log::error('Error generating payslips ZIP: ' . $e->getMessage());
            return back()->with('error', 'Failed to download payslips. Please try again.');
        }
    }
}
