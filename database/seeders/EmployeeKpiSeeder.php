<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeKpi;
use App\Models\Employee;
use App\Models\Kpi;
use App\Models\User;
use Faker\Factory as Faker;

class EmployeeKpiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all employees
        $employees = Employee::all();
        
        // Get all KPIs
        $kpis = Kpi::all();
        
        // Get managers for assigning KPIs
        $managers = User::role('manager')->get();
        
        // Assign KPIs to employees
        foreach ($employees as $employee) {
            // Get KPIs for employee's department and position
            $departmentKpis = $kpis->where('department_id', $employee->department_id);
            $positionKpis = $kpis->where('position_id', $employee->position_id);
            
            // Combine and get unique KPIs
            $relevantKpis = $departmentKpis->merge($positionKpis)->unique('id');
            
            // If no relevant KPIs, get random KPIs
            if ($relevantKpis->isEmpty()) {
                $relevantKpis = $kpis->random(min(3, $kpis->count()));
            }
            
            // Assign 2-4 KPIs to each employee
            $numKpis = min($faker->numberBetween(2, 4), $relevantKpis->count());
            $assignedKpis = $faker->randomElements($relevantKpis->toArray(), $numKpis);
            
            foreach ($assignedKpis as $kpi) {
                // Determine target value based on measurement unit
                $targetValue = 0;
                $minValue = 0;
                $maxValue = 0;
                
                switch ($kpi['measurement_unit']) {
                    case 'percentage':
                        $targetValue = $faker->numberBetween(70, 95);
                        $minValue = $faker->numberBetween(50, $targetValue - 10);
                        $maxValue = $faker->numberBetween($targetValue + 1, 100);
                        break;
                    case 'days':
                        $targetValue = $faker->numberBetween(3, 15);
                        $minValue = $faker->numberBetween(1, $targetValue - 1);
                        $maxValue = $faker->numberBetween($targetValue + 1, 30);
                        break;
                    case 'hours':
                        $targetValue = $faker->numberBetween(2, 24);
                        $minValue = $faker->numberBetween(1, $targetValue - 1);
                        $maxValue = $faker->numberBetween($targetValue + 1, 48);
                        break;
                    case 'count':
                        $targetValue = $faker->numberBetween(10, 100);
                        $minValue = $faker->numberBetween(5, $targetValue - 5);
                        $maxValue = $faker->numberBetween($targetValue + 5, 150);
                        break;
                    case 'score':
                        $targetValue = $faker->numberBetween(7, 9);
                        $minValue = $faker->numberBetween(5, $targetValue - 1);
                        $maxValue = 10;
                        break;
                    default:
                        $targetValue = $faker->numberBetween(70, 95);
                        $minValue = $faker->numberBetween(50, $targetValue - 10);
                        $maxValue = 100;
                }
                
                // Determine start and end dates based on frequency
                $startDate = now();
                $endDate = null;
                
                switch ($kpi['frequency']) {
                    case 'monthly':
                        $startDate = $faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d');
                        $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));
                        break;
                    case 'quarterly':
                        $startDate = $faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d');
                        $endDate = date('Y-m-d', strtotime($startDate . ' +3 months'));
                        break;
                    case 'annually':
                        $startDate = $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d');
                        $endDate = date('Y-m-d', strtotime($startDate . ' +1 year'));
                        break;
                    default:
                        $startDate = $faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d');
                        $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));
                }
                
                // Create employee KPI
                EmployeeKpi::create([
                    'employee_id' => $employee->id,
                    'kpi_id' => $kpi['id'],
                    'target_value' => $targetValue,
                    'minimum_value' => $minValue,
                    'maximum_value' => $maxValue,
                    'weight' => $faker->randomElement([0.5, 1.0, 1.5, 2.0]),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => $faker->randomElement(['active', 'completed', 'pending']),
                    'notes' => $faker->optional(0.7)->sentence,
                    'assigned_by' => $managers->random()->id,
                ]);
            }
        }
    }
}

