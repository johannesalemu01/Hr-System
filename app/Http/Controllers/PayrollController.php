<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Employee;
use App\Models\SalaryStructure;
use App\Models\Bonus;
use App\Models\Deduction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf; 
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule; 

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isEmployeeOnly = !$user->hasAnyRole(['super-admin', 'admin', 'hr-admin', 'manager']);

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
        $payroll = $this->getOrCreatePayroll($selectedStartDate, $selectedEndDate);


        $perPage = $request->query('per_page', 10);
        $payrollItemsQuery = PayrollItem::where('payroll_id', $payroll->id)
            ->with(['employee', 'employee.user', 'deductions', 'bonuses']);

        
        if ($isEmployeeOnly) {
            $employeeId = $user->employee->id ?? null;
            if ($employeeId) {
                $payrollItemsQuery->where('employee_id', $employeeId);
            } else {
                $payrollItemsQuery->whereRaw('1 = 0'); 
            }
        }
        

        $payrollItems = $payrollItemsQuery->paginate($perPage)
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

        
        $availableEmployees = collect(); 
        if (!$isEmployeeOnly) {
            $existingEmployeeIds = PayrollItem::where('payroll_id', $payroll->id)->pluck('employee_id')->toArray();
            Log::debug('Existing Employee IDs in Payroll ID ' . $payroll->id . ': ', $existingEmployeeIds);

            $allActiveEmployeesQuery = Employee::where(function ($query) use ($selectedEndDate) {
                    $query->whereNull('termination_date')
                          ->orWhere('termination_date', '>', $selectedEndDate);
                })
                ->where('hire_date', '<=', $selectedEndDate);

            $allActiveEmployeesData = $allActiveEmployeesQuery->get(['id', 'first_name', 'last_name', 'employee_id', 'hire_date', 'termination_date']);
            Log::debug('All Active Employees for Period ' . $selectedStartDate . ' to ' . $selectedEndDate . ': ', $allActiveEmployeesData->toArray());

            $availableEmployeesData = $allActiveEmployeesQuery
                ->whereNotIn('id', $existingEmployeeIds)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(['id', 'first_name', 'last_name', 'employee_id']);

            Log::debug('Available Employees (After Filtering Existing) for Payroll ID ' . $payroll->id . ': ', $availableEmployeesData->toArray());

            $availableEmployees = $availableEmployeesData->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'full_name' => $employee->full_name,
                    'employee_id' => $employee->employee_id,
                ];
            });
        }
        


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
            'availableEmployees' => $availableEmployees, 
            'isEmployeeView' => $isEmployeeOnly, 
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
        $user = Auth::user();

        
        if ($payrollItem->employee_id !== ($user->employee->id ?? null) && !$user->hasAnyRole(['super-admin', 'admin', 'hr-admin', 'manager'])) {
            abort(403, 'Unauthorized action.');
        }
        

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
        
        if (!Auth::user()->hasAnyRole(['super-admin', 'admin', 'hr-admin', 'manager'])) {
             abort(403, 'Unauthorized action.');
        }
        

        $payroll = Payroll::findOrFail($id);

        
        if ($payroll->status !== 'processing') {
             return redirect()->back()->with('error', 'Payroll cannot be processed in its current state.');
        }

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

    /**
     * Revert an approved payroll back to processing status.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function revertPayroll($id)
    {
        $payroll = Payroll::findOrFail($id);

        
        if ($payroll->status !== 'approved') {
            return redirect()->back()->with('error', 'Only approved payrolls can be reverted to processing.');
        }

        try {
            $payroll->status = 'processing';
            $payroll->approved_by = null; 
            $payroll->approved_at = null;
            
            
            
            $payroll->save();

            return redirect()->back()->with('success', 'Payroll reverted to processing successfully.');
        } catch (\Exception $e) {
            Log::error('Error reverting payroll: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to revert payroll status.');
        }
    }

    public function downloadPayslip($id)
    {
        $payrollItem = PayrollItem::with(['employee', 'payroll'])->findOrFail($id); 
        $user = Auth::user();

        
        if ($payrollItem->employee_id !== ($user->employee->id ?? null) && !$user->hasAnyRole(['super-admin', 'admin', 'hr-admin', 'manager'])) {
            abort(403, 'Unauthorized action.');
        }
        

        
        $pdf = Pdf::loadView('payroll.payslip_pdf', ['payrollItem' => $payrollItem]);

        return $pdf->download("Payslip_{$payrollItem->employee->employee_id}_{$payrollItem->payroll->start_date}_to_{$payrollItem->payroll->end_date}.pdf");
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

    /**
     * Remove the specified payroll item from storage.
     *
     * @param  \App\Models\PayrollItem  $item
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyItem(PayrollItem $item)
    {
        
        if ($item->payroll->status !== 'processing') {
             return redirect()->back()->with('error', 'Cannot delete items from a payroll that is not in processing status.');
        }

        
        

        try {
            
            

            $item->delete();

            
            

            return redirect()->back()->with('success', 'Payroll item deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting payroll item: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete payroll item.');
        }
    }

    /**
     * Store a newly created payroll item in storage for a specific employee.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Payroll  $payroll
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeItem(Request $request, Payroll $payroll)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $employeeId = $request->input('employee_id');

        
        $existingItem = PayrollItem::where('payroll_id', $payroll->id)
                                   ->where('employee_id', $employeeId)
                                   ->first();

        if ($existingItem) {
            return redirect()->back()->with('error', 'Employee is already in this payroll period.');
        }

        $employee = Employee::find($employeeId);

        
        if (strtotime($employee->hire_date) > strtotime($payroll->end_date) ||
            ($employee->termination_date && strtotime($employee->termination_date) < strtotime($payroll->start_date))) {
             return redirect()->back()->with('error', 'Employee was not active during this payroll period.');
        }


        $salaryStructure = SalaryStructure::where('employee_id', $employee->id)
            ->where('is_current', true)
            ->first();

        if (!$salaryStructure) {
            return redirect()->back()->with('error', 'Employee does not have a current salary structure.');
        }

        try {
            
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

            
            

            return redirect()->back()->with('success', 'Employee added to payroll successfully.');

        } catch (\Exception $e) {
            Log::error('Error adding employee to payroll: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to add employee to payroll.');
        }
    }

    /**
     * Show the form for editing the specified payroll item adjustments.
     *
     * @param  \App\Models\PayrollItem  $item
     * @return \Inertia\Response
     */
    public function editItem(PayrollItem $item)
    {
        $user = Auth::user();
        
         if ($item->employee_id !== ($user->employee->id ?? null) && !$user->hasAnyRole(['super-admin', 'admin', 'hr-admin', 'manager'])) {
             abort(403, 'Unauthorized action.');
         }
        

        
        $item->load(['employee', 'payroll', 'bonuses', 'deductions']);


        
        if ($item->payroll->status !== 'processing') {
             return redirect()->route('payroll.index', [
                 'start_date' => $item->payroll->start_date,
                 'end_date' => $item->payroll->end_date,
             ])->with('error', 'Cannot edit adjustments for a payroll that is not in processing status.');
        }


        return Inertia::render('Payroll/EditItem', [
            'payrollItem' => [
                'id' => $item->id,
                'employee_name' => $item->employee->full_name,
                'employee_id_display' => $item->employee->employee_id, 
                'payroll_period' => Carbon::parse($item->payroll->start_date)->format('M d, Y') . ' - ' . Carbon::parse($item->payroll->end_date)->format('M d, Y'),
                'basic_salary' => $item->basic_salary,
                'total_allowances' => $item->total_allowances,
                'gross_salary' => $item->gross_salary,
                'total_deductions' => $item->total_deductions,
                'total_bonuses' => $item->total_bonuses,
                'net_salary' => $item->net_salary,
                'bonuses' => $item->bonuses->map(fn($b) => [
                    'id' => $b->id,
                    'type' => $b->bonus_type,
                    'description' => $b->description,
                    'amount' => $b->amount,
                    'is_manual' => !in_array($b->bonus_type, ['performance', 'overtime']) 
                ]),
                'deductions' => $item->deductions->map(fn($d) => [
                    'id' => $d->id,
                    'type' => $d->deduction_type,
                    'description' => $d->description,
                    'amount' => $d->amount,
                    'is_manual' => !in_array($d->deduction_type, ['tax', 'pension', 'health_insurance']) 
                ]),
            ]
        ]);
    }

    /**
     * Add a manual bonus to a payroll item.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PayrollItem  $item
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addBonus(Request $request, PayrollItem $item)
    {
         $user = Auth::user();
         
         if ($item->employee_id !== ($user->employee->id ?? null) && !$user->hasAnyRole(['super-admin', 'admin', 'hr-admin', 'manager'])) {
             return back()->with('error', 'Unauthorized action.');
         }
         

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'bonus_type' => ['required', 'string', Rule::in(['manual_adjustment', 'other'])], 
        ]);

        try {
            $item->bonuses()->create($validated);
            $this->updatePayrollItemTotals($item); 
            return back()->with('success', 'Bonus added successfully.');
        } catch (\Exception $e) {
            Log::error("Error adding bonus to PayrollItem ID {$item->id}: " . $e->getMessage());
            return back()->with('error', 'Failed to add bonus.');
        }
    }

    /**
     * Delete a manual bonus.
     *
     * @param  \App\Models\Bonus  $bonus
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteBonus(Bonus $bonus)
    {
        $payrollItem = $bonus->payrollItem; 

         if ($payrollItem->payroll->status !== 'processing') {
             return back()->with('error', 'Cannot delete adjustments when payroll is not processing.');
         }

        
        
        
        

        try {
            $bonus->delete();
            $this->updatePayrollItemTotals($payrollItem); 
            return back()->with('success', 'Bonus deleted successfully.');
        } catch (\Exception $e) {
            Log::error("Error deleting Bonus ID {$bonus->id}: " . $e->getMessage());
            return back()->with('error', 'Failed to delete bonus.');
        }
    }

    /**
     * Add a manual deduction to a payroll item.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PayrollItem  $item
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addDeduction(Request $request, PayrollItem $item)
    {
         $user = Auth::user();
         
         if ($item->employee_id !== ($user->employee->id ?? null) && !$user->hasAnyRole(['super-admin', 'admin', 'hr-admin', 'manager'])) {
             return back()->with('error', 'Unauthorized action.');
         }
         

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'deduction_type' => ['required', 'string', Rule::in(['loan_repayment', 'advance', 'manual_adjustment', 'other'])], 
        ]);

        try {
            $item->deductions()->create($validated);
            $this->updatePayrollItemTotals($item); 
            return back()->with('success', 'Deduction added successfully.');
        } catch (\Exception $e) {
            Log::error("Error adding deduction to PayrollItem ID {$item->id}: " . $e->getMessage());
            return back()->with('error', 'Failed to add deduction.');
        }
    }

    /**
     * Delete a manual deduction.
     *
     * @param  \App\Models\Deduction  $deduction
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteDeduction(Deduction $deduction)
    {
        $payrollItem = $deduction->payrollItem; 

         if ($payrollItem->payroll->status !== 'processing') {
             return back()->with('error', 'Cannot delete adjustments when payroll is not processing.');
         }

        
        
        
        

        try {
            $deduction->delete();
            $this->updatePayrollItemTotals($payrollItem); 
            return back()->with('success', 'Deduction deleted successfully.');
        } catch (\Exception $e) {
            Log::error("Error deleting Deduction ID {$deduction->id}: " . $e->getMessage());
            return back()->with('error', 'Failed to delete deduction.');
        }
    }
}
