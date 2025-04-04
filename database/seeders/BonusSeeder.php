<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bonus;
use App\Models\PayrollItem;
use App\Models\KpiRecord;
use App\Models\Employee;
use Faker\Factory as Faker;

class BonusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all payroll items
        $payrollItems = PayrollItem::all();
        
        // Bonus types
        $bonusTypes = [
            'performance' => [0.05, 0.15],
            'attendance' => [0.02, 0.05],
            'overtime' => [0.03, 0.08],
            'holiday' => [0.05, 0.1],
            'project_completion' => [0.05, 0.15],
            'other' => [0.01, 0.05],
        ];
        
        // Create bonuses for each payroll item
        foreach ($payrollItems as $payrollItem) {
            $totalBonuses = 0;
            
            // Get employee
            $employee = Employee::find($payrollItem->employee_id);
            
            // Get payroll period
            $payroll = $payrollItem->payroll;
            
            // 30% chance for performance bonus
            if ($faker->boolean(30)) {
                // Check if employee has KPI records in this period
                $kpiRecords = KpiRecord::whereHas('employeeKpi', function ($query) use ($employee) {
                    $query->where('employee_id', $employee->id);
                })
                ->whereBetween('record_date', [$payroll->start_date, $payroll->end_date])
                ->get();
                
                if ($kpiRecords->count() > 0) {
                    $performanceRate = $faker->randomFloat(4, $bonusTypes['performance'][0], $bonusTypes['performance'][1]);
                    $performanceAmount = $payrollItem->basic_salary * $performanceRate;
                    $totalBonuses += $performanceAmount;
                    
                    Bonus::create([
                        'payroll_item_id' => $payrollItem->id,
                        'bonus_type' => 'performance',
                        'description' => 'Performance Bonus',
                        'amount' => $performanceAmount,
                    ]);
                }
            }
            
            // 20% chance for attendance bonus if absent days = 0
            if ($faker->boolean(20) && $payrollItem->absent_days === 0) {
                $attendanceRate = $faker->randomFloat(4, $bonusTypes['attendance'][0], $bonusTypes['attendance'][1]);
                $attendanceAmount = $payrollItem->basic_salary * $attendanceRate;
                $totalBonuses += $attendanceAmount;
                
                Bonus::create([
                    'payroll_item_id' => $payrollItem->id,
                    'bonus_type' => 'attendance',
                    'description' => 'Perfect Attendance Bonus',
                    'amount' => $attendanceAmount,
                ]);
            }
            
            // 25% chance for overtime bonus
            if ($faker->boolean(25)) {
                $overtimeRate = $faker->randomFloat(4, $bonusTypes['overtime'][0], $bonusTypes['overtime'][1]);
                $overtimeAmount = $payrollItem->basic_salary * $overtimeRate;
                $totalBonuses += $overtimeAmount;
                
                Bonus::create([
                    'payroll_item_id' => $payrollItem->id,
                    'bonus_type' => 'overtime',
                    'description' => 'Overtime Bonus',
                    'amount' => $overtimeAmount,
                ]);
            }
            
            // 10% chance for holiday bonus (more likely in December)
            $isDecember = date('m', strtotime($payroll->end_date)) === '12';
            if (($isDecember && $faker->boolean(50)) || (!$isDecember && $faker->boolean(10))) {
                $holidayRate = $faker->randomFloat(4, $bonusTypes['holiday'][0], $bonusTypes['holiday'][1]);
                $holidayAmount = $payrollItem->basic_salary * $holidayRate;
                $totalBonuses += $holidayAmount;
                
                Bonus::create([
                    'payroll_item_id' => $payrollItem->id,
                    'bonus_type' => 'holiday',
                    'description' => $isDecember ? 'Christmas Bonus' : 'Holiday Bonus',
                    'amount' => $holidayAmount,
                ]);
            }
            
            // 15% chance for project completion bonus
            if ($faker->boolean(15)) {
                $projectRate = $faker->randomFloat(4, $bonusTypes['project_completion'][0], $bonusTypes['project_completion'][1]);
                $projectAmount = $payrollItem->basic_salary * $projectRate;
                $totalBonuses += $projectAmount;
                
                Bonus::create([
                    'payroll_item_id' => $payrollItem->id,
                    'bonus_type' => 'project_completion',
                    'description' => 'Project Completion Bonus',
                    'amount' => $projectAmount,
                ]);
            }
            
            // 5% chance for other bonus
            if ($faker->boolean(5)) {
                $otherRate = $faker->randomFloat(4, $bonusTypes['other'][0], $bonusTypes['other'][1]);
                $otherAmount = $payrollItem->basic_salary * $otherRate;
                $totalBonuses += $otherAmount;
                
                Bonus::create([
                    'payroll_item_id' => $payrollItem->id,
                    'bonus_type' => 'other',
                    'description' => $faker->randomElement(['Referral Bonus', 'Innovation Award', 'Special Achievement', 'Loyalty Bonus']),
                    'amount' => $otherAmount,
                ]);
            }
            
            // Update payroll item with total bonuses and net salary
            $payrollItem->total_bonuses = $totalBonuses;
            $payrollItem->net_salary = $payrollItem->gross_salary - $payrollItem->total_deductions + $totalBonuses;
            $payrollItem->save();
        }
    }
}

