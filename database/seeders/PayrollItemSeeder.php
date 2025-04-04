<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PayrollItem;
use App\Models\Payroll;
use App\Models\Employee;
use App\Models\SalaryStructure;
use Faker\Factory as Faker;

class PayrollItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all payrolls
        $payrolls = Payroll::all();
        
        // Get all employees
        $employees = Employee::all();
        
        // Create payroll items for each payroll
        foreach ($payrolls as $payroll) {
            foreach ($employees as $employee) {
                // Skip if employee was hired after payroll period
                if (strtotime($employee->hire_date) > strtotime($payroll->end_date)) {
                    continue;
                }
                
                // Skip if employee was terminated before payroll period
                if ($employee->termination_date && strtotime($employee->termination_date) < strtotime($payroll->start_date)) {
                    continue;
                }
                
                // Get salary structure effective during payroll period
                $salaryStructure = SalaryStructure::where('employee_id', $employee->id)
                    ->where('effective_date', '<=', $payroll->end_date)
                    ->where(function ($query) use ($payroll) {
                        $query->whereNull('end_date')
                            ->orWhere('end_date', '>=', $payroll->start_date);
                    })
                    ->first();
                
                // Skip if no salary structure found
                if (!$salaryStructure) {
                    continue;
                }
                
                // Calculate total allowances
                $totalAllowances = $salaryStructure->housing_allowance +
                    $salaryStructure->transport_allowance +
                    $salaryStructure->meal_allowance +
                    $salaryStructure->medical_allowance +
                    $salaryStructure->other_allowances;
                
                // Calculate working days, leave days, and absent days
                $workingDays = $faker->numberBetween(18, 22); // Assuming ~22 working days per month
                $leaveDays = $faker->numberBetween(0, 3);
                $absentDays = $faker->numberBetween(0, 2);
                
                // Calculate total deductions (will be populated by DeductionSeeder)
                $totalDeductions = 0;
                
                // Calculate total bonuses (will be populated by BonusSeeder)
                $totalBonuses = 0;
                
                // Calculate gross salary
                $grossSalary = $salaryStructure->basic_salary + $totalAllowances;
                
                // Calculate net salary (will be updated after deductions and bonuses)
                $netSalary = $grossSalary;
                
                // Create payroll item
                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'employee_id' => $employee->id,
                    'basic_salary' => $salaryStructure->basic_salary,
                    'total_allowances' => $totalAllowances,
                    'total_deductions' => $totalDeductions,
                    'total_bonuses' => $totalBonuses,
                    'gross_salary' => $grossSalary,
                    'net_salary' => $netSalary,
                    'working_days' => $workingDays,
                    'leave_days' => $leaveDays,
                    'absent_days' => $absentDays,
                    'notes' => $faker->optional(0.2)->sentence,
                ]);
            }
        }
    }
}

