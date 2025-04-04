<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Point;
use App\Models\Employee;
use App\Models\KpiRecord;
use App\Models\EmployeeBadge;
use App\Models\User;
use Faker\Factory as Faker;

class PointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all employees
        $employees = Employee::all();
        
        // Get managers for awarding points
        $managers = User::role('manager')->get();
        
        // Award points from KPI records
        $kpiRecords = KpiRecord::all();
        foreach ($kpiRecords as $kpiRecord) {
            // Skip if no points earned
            if ($kpiRecord->points_earned <= 0) {
                continue;
            }
            
            Point::create([
                'employee_id' => $kpiRecord->employeeKpi->employee_id,
                'points' => $kpiRecord->points_earned,
                'source_type' => 'kpi',
                'source_id' => $kpiRecord->id,
                'description' => 'Points earned from KPI: ' . $kpiRecord->employeeKpi->kpi->name,
                'awarded_by' => $kpiRecord->recorded_by,
            ]);
        }
        
        // Award points from badges
        $employeeBadges = EmployeeBadge::all();
        foreach ($employeeBadges as $employeeBadge) {
            Point::create([
                'employee_id' => $employeeBadge->employee_id,
                'points' => $employeeBadge->badge->points_required,
                'source_type' => 'badge',
                'source_id' => $employeeBadge->id,
                'description' => 'Points earned from badge: ' . $employeeBadge->badge->name,
                'awarded_by' => $employeeBadge->awarded_by,
            ]);
        }
        
        // Award some random bonus points
        foreach ($employees as $employee) {
            // 30% chance to get bonus points
            if ($faker->boolean(30)) {
                Point::create([
                    'employee_id' => $employee->id,
                    'points' => $faker->numberBetween(5, 25),
                    'source_type' => 'bonus',
                    'source_id' => null,
                    'description' => $faker->randomElement([
                        'Bonus points for exceptional work',
                        'Recognition for going above and beyond',
                        'Special project completion bonus',
                        'Team collaboration bonus',
                        'Customer feedback recognition',
                    ]),
                    'awarded_by' => $managers->random()->id,
                ]);
            }
        }
    }
}

