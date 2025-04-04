<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Deduction;
use App\Models\PayrollItem;
use Faker\Factory as Faker;

class DeductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all payroll items
        $payrollItems = PayrollItem::all();
        
        // Deduction types
        $deductionTypes = [
            'tax' => [0.05, 0.1],
            'pension' => [0.03, 0.05],
            'health_insurance' => [0.02, 0.03],
            'loan_repayment' => [0.05, 0.1],
            'absence' => [0.01, 0.03],
            'other' => [0.01, 0.02],
        ];
        
        // Create deductions for each payroll item
        foreach ($payrollItems as $payrollItem) {
            $totalDeductions = 0;
            
            // Always add tax deduction
            $taxRate = $faker->randomFloat(4, $deductionTypes['tax'][0], $deductionTypes['tax'][1]);
            $taxAmount = $payrollItem->gross_salary * $taxRate;
            $totalDeductions += $taxAmount;
            
            Deduction::create([
                'payroll_item_id' => $payrollItem->id,
                'deduction_type' => 'tax',
                'description' => 'Income Tax',
                'amount' => $taxAmount,
            ]);
            
            // Always add pension deduction
            $pensionRate = $faker->randomFloat(4, $deductionTypes['pension'][0], $deductionTypes['pension'][1]);
            $pensionAmount = $payrollItem->gross_salary * $pensionRate;
            $totalDeductions += $pensionAmount;
            
            Deduction::create([
                'payroll_item_id' => $payrollItem->id,
                'deduction_type' => 'pension',
                'description' => 'Pension Contribution',
                'amount' => $pensionAmount,
            ]);
            
            // Add health insurance deduction
            $healthRate = $faker->randomFloat(4, $deductionTypes['health_insurance'][0], $deductionTypes['health_insurance'][1]);
            $healthAmount = $payrollItem->gross_salary * $healthRate;
            $totalDeductions += $healthAmount;
            
            Deduction::create([
                'payroll_item_id' => $payrollItem->id,
                'deduction_type' => 'health_insurance',
                'description' => 'Health Insurance Premium',
                'amount' => $healthAmount,
            ]);
            
            // 20% chance for loan repayment
            if ($faker->boolean(20)) {
                $loanRate = $faker->randomFloat(4, $deductionTypes['loan_repayment'][0], $deductionTypes['loan_repayment'][1]);
                $loanAmount = $payrollItem->gross_salary * $loanRate;
                $totalDeductions += $loanAmount;
                
                Deduction::create([
                    'payroll_item_id' => $payrollItem->id,
                    'deduction_type' => 'loan_repayment',
                    'description' => 'Loan Repayment',
                    'amount' => $loanAmount,
                ]);
            }
            
            // Add absence deduction if absent days > 0
            if ($payrollItem->absent_days > 0) {
                $absenceRate = $faker->randomFloat(4, $deductionTypes['absence'][0], $deductionTypes['absence'][1]);
                $absenceAmount = $payrollItem->gross_salary * $absenceRate * $payrollItem->absent_days;
                $totalDeductions += $absenceAmount;
                
                Deduction::create([
                    'payroll_item_id' => $payrollItem->id,
                    'deduction_type' => 'absence',
                    'description' => 'Absence Deduction',
                    'amount' => $absenceAmount,
                ]);
            }
            
            // 10% chance for other deduction
            if ($faker->boolean(10)) {
                $otherRate = $faker->randomFloat(4, $deductionTypes['other'][0], $deductionTypes['other'][1]);
                $otherAmount = $payrollItem->gross_salary * $otherRate;
                $totalDeductions += $otherAmount;
                
                Deduction::create([
                    'payroll_item_id' => $payrollItem->id,
                    'deduction_type' => 'other',
                    'description' => $faker->randomElement(['Professional Membership', 'Equipment Damage', 'Advance Salary Recovery', 'Uniform Cost']),
                    'amount' => $otherAmount,
                ]);
            }
            
            // Update payroll item with total deductions and net salary
            $payrollItem->total_deductions = $totalDeductions;
            $payrollItem->net_salary = $payrollItem->gross_salary - $totalDeductions + $payrollItem->total_bonuses;
            $payrollItem->save();
        }
    }
}

