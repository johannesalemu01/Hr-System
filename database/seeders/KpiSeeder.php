<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kpi;
use App\Models\Department;
use App\Models\Position;
use Faker\Factory as Faker;

class KpiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all departments
        $departments = Department::all();
        
        // Get all positions
        $positions = Position::all();
        
        // KPI data by department
        $kpisByDepartment = [
            'HR' => [
                [
                    'name' => 'Time to Fill Positions',
                    'description' => 'Average time taken to fill open positions',
                    'measurement_unit' => 'days',
                    'frequency' => 'monthly',
                    'points_value' => 10,
                ],
                [
                    'name' => 'Employee Turnover Rate',
                    'description' => 'Percentage of employees who leave the company',
                    'measurement_unit' => 'percentage',
                    'frequency' => 'quarterly',
                    'points_value' => 15,
                ],
                [
                    'name' => 'Training Completion Rate',
                    'description' => 'Percentage of employees who complete required training',
                    'measurement_unit' => 'percentage',
                    'frequency' => 'quarterly',
                    'points_value' => 10,
                ],
            ],
            'IT' => [
                [
                    'name' => 'System Uptime',
                    'description' => 'Percentage of time systems are operational',
                    'measurement_unit' => 'percentage',
                    'frequency' => 'monthly',
                    'points_value' => 20,
                ],
                [
                    'name' => 'Ticket Resolution Time',
                    'description' => 'Average time to resolve support tickets',
                    'measurement_unit' => 'hours',
                    'frequency' => 'monthly',
                    'points_value' => 15,
                ],
                [
                    'name' => 'Project Delivery On Time',
                    'description' => 'Percentage of projects delivered on schedule',
                    'measurement_unit' => 'percentage',
                    'frequency' => 'quarterly',
                    'points_value' => 25,
                ],
            ],
            'FIN' => [
                [
                    'name' => 'Budget Variance',
                    'description' => 'Difference between actual and budgeted expenses',
                    'measurement_unit' => 'percentage',
                    'frequency' => 'monthly',
                    'points_value' => 20,
                ],
                [
                    'name' => 'Invoice Processing Time',
                    'description' => 'Average time to process invoices',
                    'measurement_unit' => 'days',
                    'frequency' => 'monthly',
                    'points_value' => 15,
                ],
                [
                    'name' => 'Financial Report Accuracy',
                    'description' => 'Percentage of financial reports without errors',
                    'measurement_unit' => 'percentage',
                    'frequency' => 'quarterly',
                    'points_value' => 25,
                ],
            ],
            'MKT' => [
                [
                    'name' => 'Lead Generation',
                    'description' => 'Number of new leads generated',
                    'measurement_unit' => 'count',
                    'frequency' => 'monthly',
                    'points_value' => 15,
                ],
                [
                    'name' => 'Conversion Rate',
                    'description' => 'Percentage of leads converted to customers',
                    'measurement_unit' => 'percentage',
                    'frequency' => 'monthly',
                    'points_value' => 20,
                ],
                [
                    'name' => 'Social Media Engagement',
                    'description' => 'Level of engagement on social media platforms',
                    'measurement_unit' => 'score',
                    'frequency' => 'monthly',
                    'points_value' => 10,
                ],
            ],
            'OPS' => [
                [
                    'name' => 'Production Efficiency',
                    'description' => 'Ratio of output to input resources',
                    'measurement_unit' => 'percentage',
                    'frequency' => 'monthly',
                    'points_value' => 20,
                ],
                [
                    'name' => 'Quality Control',
                    'description' => 'Percentage of products passing quality checks',
                    'measurement_unit' => 'percentage',
                    'frequency' => 'monthly',
                    'points_value' => 25,
                ],
                [
                    'name' => 'Delivery Time',
                    'description' => 'Average time to deliver products',
                    'measurement_unit' => 'days',
                    'frequency' => 'monthly',
                    'points_value' => 15,
                ],
            ],
            'SLS' => [
                [
                    'name' => 'Sales Target Achievement',
                    'description' => 'Percentage of sales target achieved',
                    'measurement_unit' => 'percentage',
                    'frequency' => 'monthly',
                    'points_value' => 25,
                ],
                [
                    'name' => 'New Customer Acquisition',
                    'description' => 'Number of new customers acquired',
                    'measurement_unit' => 'count',
                    'frequency' => 'monthly',
                    'points_value' => 20,
                ],
                [
                    'name' => 'Customer Retention Rate',
                    'description' => 'Percentage of customers retained',
                    'measurement_unit' => 'percentage',
                    'frequency' => 'quarterly',
                    'points_value' => 15,
                ],
            ],
        ];
        
        // Create KPIs for each department
        foreach ($departments as $department) {
            $departmentKpis = $kpisByDepartment[$department->code] ?? [];
            
            foreach ($departmentKpis as $kpiData) {
                // Get positions for this department
                $departmentPositions = $positions->where('department_id', $department->id);
                
                // Create KPI
                Kpi::create([
                    'name' => $kpiData['name'],
                    'description' => $kpiData['description'],
                    'measurement_unit' => $kpiData['measurement_unit'],
                    'frequency' => $kpiData['frequency'],
                    'department_id' => $department->id,
                    'position_id' => $departmentPositions->random()->id,
                    'is_active' => true,
                    'points_value' => $kpiData['points_value'],
                ]);
            }
            
            // Add some position-specific KPIs
            foreach ($departmentPositions as $position) {
                // 50% chance to create a position-specific KPI
                if ($faker->boolean(50)) {
                    Kpi::create([
                        'name' => $position->title . ' ' . $faker->randomElement(['Efficiency', 'Performance', 'Quality', 'Productivity']),
                        'description' => 'KPI specific to ' . $position->title . ' role',
                        'measurement_unit' => $faker->randomElement(['percentage', 'score', 'count', 'days', 'hours']),
                        'frequency' => $faker->randomElement(['monthly', 'quarterly', 'annually']),
                        'department_id' => $department->id,
                        'position_id' => $position->id,
                        'is_active' => true,
                        'points_value' => $faker->numberBetween(5, 30),
                    ]);
                }
            }
        }
    }
}

