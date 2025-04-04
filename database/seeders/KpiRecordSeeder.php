<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KpiRecord;
use App\Models\EmployeeKpi;
use App\Models\User;
use Faker\Factory as Faker;

class KpiRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all employee KPIs
        $employeeKpis = EmployeeKpi::all();
        
        // Get managers for recording KPIs
        $managers = User::role('manager')->get();
        
        // Create KPI records
        foreach ($employeeKpis as $employeeKpi) {
            // Skip if KPI is pending
            if ($employeeKpi->status === 'pending') {
                continue;
            }
            
            // Determine number of records based on frequency and status
            $numRecords = 1;
            
            if ($employeeKpi->status === 'completed') {
                switch ($employeeKpi->kpi->frequency) {
                    case 'monthly':
                        $numRecords = $faker->numberBetween(2, 4);
                        break;
                    case 'quarterly':
                        $numRecords = $faker->numberBetween(1, 3);
                        break;
                    case 'annually':
                        $numRecords = 1;
                        break;
                    default:
                        $numRecords = $faker->numberBetween(1, 3);
                }
            }
            
            // Create records
            for ($i = 0; $i < $numRecords; $i++) {
                // Calculate record date
                $recordDate = null;
                
                if ($numRecords > 1) {
                    // Spread records over the period
                    $interval = (strtotime($employeeKpi->end_date) - strtotime($employeeKpi->start_date)) / ($numRecords + 1);
                    $recordDate = date('Y-m-d', strtotime($employeeKpi->start_date) + ($interval * ($i + 1)));
                } else {
                    $recordDate = $faker->dateTimeBetween($employeeKpi->start_date, min($employeeKpi->end_date, now()))->format('Y-m-d');
                }
                
                // Calculate actual value
                $actualValue = 0;
                $achievementPercentage = 0;
                
                if ($employeeKpi->status === 'completed') {
                    // For completed KPIs, actual value is likely to be close to or above target
                    $actualValue = $faker->randomFloat(2, $employeeKpi->target_value * 0.9, $employeeKpi->maximum_value);
                    $achievementPercentage = min(100, ($actualValue / $employeeKpi->target_value) * 100);
                } else {
                    // For active KPIs, actual value is more variable
                    $actualValue = $faker->randomFloat(2, $employeeKpi->minimum_value, $employeeKpi->maximum_value);
                    $achievementPercentage = min(100, ($actualValue / $employeeKpi->target_value) * 100);
                }
                
                // Calculate points earned
                $pointsEarned = round(($achievementPercentage / 100) * $employeeKpi->kpi->points_value);
                
                // Create KPI record
                KpiRecord::create([
                    'employee_kpi_id' => $employeeKpi->id,
                    'actual_value' => $actualValue,
                    'achievement_percentage' => $achievementPercentage,
                    'record_date' => $recordDate,
                    'comments' => $faker->optional(0.7)->sentence,
                    'recorded_by' => $managers->random()->id,
                    'points_earned' => $pointsEarned,
                ]);
            }
        }
    }
}

