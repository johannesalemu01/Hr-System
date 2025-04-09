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

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        // Get the current payroll period or default to the latest
        $currentPayroll = Payroll::orderBy('end_date', 'desc')->first();
        
        // If no payroll exists yet, create a default period (current month)
        if (!$currentPayroll) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        } else {
            $startDate = $currentPayroll->start_date;
            $endDate = $currentPayroll->end_date;
        }
        
        // Allow overriding with query parameters
        $selectedStartDate = $request->query('start_date', $startDate->format('Y-m-d'));
        $selectedEndDate = $request->query('end_date', $endDate->format('Y-m-d'));
        
        // Get the payroll for the selected period
        $payroll = Payroll::where('start_date', $selectedStartDate)
            ->where('end_date', $selectedEndDate)
            ->first();
            
        // If no payroll exists for the selected period, get or create one
        if (!$payroll) {
            $payroll = $this->getOrCreatePayroll($selectedStartDate, $selectedEndDate);
        }
        
        // Get payroll items with related data
        $perPage = $request->query('per_page', 10);
        $payrollItems = PayrollItem::where('payroll_id', $payroll->id)
            ->with(['employee', 'employee.user', 'deductions', 'bonuses'])
            ->paginate($perPage)
            ->through(function ($item) {
                // Calculate overtime amount (sum of relevant bonuses)
                $overtimeAmount = $item->bonuses->where('bonus_type', 'overtime')->sum('amount');
                
                // Calculate cash advance (sum of relevant deductions)
                $cashAdvance = $item->deductions->where('deduction_type', 'advance')->sum('amount');
                
                return [
                    'id' => $item->id,
                    'employee' => [
                        'id' => $item->employee->id,
                        'name' => $item->employee->full_name,
                        'employee_id' => $item->employee->employee_id,
                        'profile_picture' => $item->employee->profile_picture ?? '/placeholder.svg?height=40&width=40',
                    ],
                    'gross' => $item->gross_salary,
                    'deductions' => $item->total_deductions,
                    'cash_advance' => $cashAdvance,
                    'overtime' => $overtimeAmount,
                    'net_pay' => $item->net_salary,
                ];
            });
            
        // Get all available payroll periods for the dropdown
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
            
        return Inertia::render('Payroll/index', [
            'payrollItems' => $payrollItems,
            'payrollPeriods' => $payrollPeriods,
            'currentPeriod' => [
                'start_date' => $selectedStartDate,
                'end_date' => $selectedEndDate,
                'formatted' => Carbon::parse($selectedStartDate)->format('M d, Y') . ' - ' . 
                              Carbon::parse($selectedEndDate)->format('M d, Y'),
            ],
            'payroll' => [
                'id' => $payroll->id,
                'status' => $payroll->status,
                'payment_date' => $payroll->payment_date,
            ],
        ]);
    }
    
    private function getOrCreatePayroll($startDate, $endDate)
    {
        // Check if a payroll already exists for this period
        $payroll = Payroll::where('start_date', $startDate)
            ->where('end_date', $endDate)
            ->first();
            
        if ($payroll) {
            return $payroll;
        }
        
        // Create a new payroll
        $payroll = Payroll::create([
            'payroll_reference' => 'PAY-' . Carbon::parse($startDate)->format('Ym'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'payment_date' => Carbon::parse($endDate)->addDays(5)->format('Y-m-d'),
            'status' => 'processing',
            'created_by' => Auth::id(),
        ]);
        
        // Generate payroll items for all active employees
        $this->generatePayrollItems($payroll);
        
        return $payroll;
    }
    
    private function generatePayrollItems($payroll)
    {
        // Get all active employees
        $employees = Employee::whereNull('termination_date')
            ->orWhere('termination_date', '>', $payroll->end_date)
            ->get();
            
        foreach ($employees as $employee) {
            // Skip if employee was hired after payroll period
            if (strtotime($employee->hire_date) > strtotime($payroll->end_date)) {
                continue;
            }
            
            // Get current salary structure
            $salaryStructure = SalaryStructure::where('employee_id', $employee->id)
                ->where('is_current', true)
                ->first();
                
            if (!$salaryStructure) {
                continue; // Skip if no salary structure
            }
            
            // Calculate total allowances
            $totalAllowances = $salaryStructure->housing_allowance +
                $salaryStructure->transport_allowance +
                $salaryStructure->meal_allowance +
                $salaryStructure->medical_allowance +
                $salaryStructure->other_allowances;
                
            // Calculate gross salary
            $grossSalary = $salaryStructure->basic_salary + $totalAllowances;
            
            // Create payroll item
            $payrollItem = PayrollItem::create([
                'payroll_id' => $payroll->id,
                'employee_id' => $employee->id,
                'basic_salary' => $salaryStructure->basic_salary,
                'total_allowances' => $totalAllowances,
                'total_deductions' => 0, // Will be updated later
                'total_bonuses' => 0, // Will be updated later
                'gross_salary' => $grossSalary,
                'net_salary' => $grossSalary, // Will be updated after deductions and bonuses
                'working_days' => $this->calculateWorkingDays($payroll->start_date, $payroll->end_date, $employee->id),
            ]);
            
            // Generate standard deductions
            $this->generateStandardDeductions($payrollItem);
            
            // Generate standard bonuses
            $this->generateStandardBonuses($payrollItem);
            
            // Update totals
            $this->updatePayrollItemTotals($payrollItem);
        }
    }
    
    private function calculateWorkingDays($startDate, $endDate, $employeeId)
    {
        // Count working days excluding weekends and approved leaves
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $workingDays = 0;
        for ($date = $start; $date->lte($end); $date->addDay()) {
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }
            
            // Check if employee is on approved leave
            $onLeave = \App\Models\LeaveRequest::where('employee_id', $employeeId)
                ->where('status', 'approved')
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->exists();
                
            if (!$onLeave) {
                $workingDays++;
            }
        }
        
        return $workingDays;
    }
    
    private function generateStandardDeductions($payrollItem)
    {
        // Tax deduction (e.g., 10% of gross)
        Deduction::create([
            'payroll_item_id' => $payrollItem->id,
            'deduction_type' => 'tax',
            'description' => 'Income Tax',
            'amount' => $payrollItem->gross_salary * 0.1, // 10% tax
        ]);
        
        // Pension deduction (e.g., 5% of basic salary)
        Deduction::create([
            'payroll_item_id' => $payrollItem->id,
            'deduction_type' => 'pension',
            'description' => 'Pension Contribution',
            'amount' => $payrollItem->basic_salary * 0.05, // 5% pension
        ]);
        
        // Health insurance (e.g., 3% of basic salary)
        Deduction::create([
            'payroll_item_id' => $payrollItem->id,
            'deduction_type' => 'health_insurance',
            'description' => 'Health Insurance',
            'amount' => $payrollItem->basic_salary * 0.03, // 3% health insurance
        ]);
    }
    
    private function generateStandardBonuses($payrollItem)
    {
        // Example: Performance bonus based on KPI scores
        $employee = Employee::find($payrollItem->employee_id);
        
        // Get average KPI score for the period
        $avgKpiScore = \App\Models\KpiRecord::whereHas('employeeKpi', function($query) use ($employee) {
            $query->where('employee_id', $employee->id);
        })
        ->whereBetween('record_date', [$payrollItem->payroll->start_date, $payrollItem->payroll->end_date])
        ->avg('achievement_percentage');
        
        // If KPI score is above 90%, add performance bonus
        if ($avgKpiScore > 90) {
            Bonus::create([
                'payroll_item_id' => $payrollItem->id,
                'bonus_type' => 'performance',
                'description' => 'Performance Bonus',
                'amount' => $payrollItem->basic_salary * 0.05, // 5% bonus
            ]);
        }
    }
    
    private function updatePayrollItemTotals($payrollItem)
    {
        // Calculate total deductions
        $totalDeductions = Deduction::where('payroll_item_id', $payrollItem->id)->sum('amount');
        
        // Calculate total bonuses
        $totalBonuses = Bonus::where('payroll_item_id', $payrollItem->id)->sum('amount');
        
        // Update payroll item
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
        
        return Inertia::render('Payroll/Payslip', [
            'payrollItem' => [
                'id' => $payrollItem->id,
                'employee' => [
                    'id' => $payrollItem->employee->id,
                    'name' => $payrollItem->employee->full_name,
                    'employee_id' => $payrollItem->employee->employee_id,
                    'department' => $payrollItem->employee->department->name,
                    'position' => $payrollItem->employee->position->title,
                    'join_date' => $payrollItem->employee->hire_date,
                    'bank_name' => $payrollItem->employee->bank_name,
                    'bank_account' => $payrollItem->employee->bank_account_number,
                ],
                'payroll_period' => [
                    'start_date' => $payrollItem->payroll->start_date,
                    'end_date' => $payrollItem->payroll->end_date,
                    'payment_date' => $payrollItem->payroll->payment_date,
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
                'working_days' => $payrollItem->working_days,
            ]
        ]);
    }
    
    public function processPayroll($id)
    {
        $payroll = Payroll::findOrFail($id);
        
        // Update status to 'approved'
        $payroll->status = 'approved';
        $payroll->approved_by = Auth::id();
        $payroll->approved_at = now();
        $payroll->save();
        
        return redirect()->back()->with('success', 'Payroll has been processed successfully.');
    }
    
    public function releasePayroll($id)
    {
        $payroll = Payroll::findOrFail($id);
        
        // Check if payroll is approved
        if ($payroll->status !== 'approved') {
            return redirect()->back()->with('error', 'Payroll must be approved before it can be released.');
        }
        
        // Update status to 'paid'
        $payroll->status = 'paid';
        $payroll->save();
        
        return redirect()->back()->with('success', 'Payroll has been released successfully.');
    }
}