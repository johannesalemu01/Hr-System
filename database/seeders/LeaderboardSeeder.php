<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Leaderboard;
use App\Models\Department;
use Faker\Factory as Faker;

class LeaderboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all departments
        $departments = Department::all();
        
        // Create company-wide leaderboard
        Leaderboard::create([
            'name' => 'Company-wide Performance',
            'description' => 'Leaderboard for all employees across the company',
            'period_type' => 'monthly',
            'start_date' => date('Y-m-01'),
            'end_date' => date('Y-m-t'),
            'department_id' => null,
            'is_active' => true,
        ]);
        
        // Create quarterly company-wide leaderboard
        Leaderboard::create([
            'name' => 'Quarterly Company Performance',
            'description' => 'Quarterly leaderboard for all employees',
            'period_type' => 'quarterly',
            'start_date' => date('Y-m-01', strtotime('first day of this quarter')),
            'end_date' => date('Y-m-t', strtotime('last day of this quarter')),
            'department_id' => null,
            'is_active' => true,
        ]);
        
        // Create department leaderboards
        foreach ($departments as $department) {
            Leaderboard::create([
                'name' => $department->name . ' Monthly Performance',
                'description' => 'Monthly leaderboard for ' . $department->name . ' department',
                'period_type' => 'monthly',
                'start_date' => date('Y-m-01'),
                'end_date' => date('Y-m-t'),
                'department_id' => $department->id,
                'is_active' => true,
            ]);
            
            // 50% chance to create a quarterly department leaderboard
            if ($faker->boolean(50)) {
                Leaderboard::create([
                    'name' => $department->name . ' Quarterly Performance',
                    'description' => 'Quarterly leaderboard for ' . $department->name . ' department',
                    'period_type' => 'quarterly',
                    'start_date' => date('Y-m-01', strtotime('first day of this quarter')),
                    'end_date' => date('Y-m-t', strtotime('last day of this quarter')),
                    'department_id' => $department->id,
                    'is_active' => true,
                ]);
            }
        }
    }
}

