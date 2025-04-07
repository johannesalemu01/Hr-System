<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;
use App\Models\Department;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = Department::all();
        
        $positions = [
            // HR positions
            [
                'title' => 'HR Manager',
                'description' => 'Manages the HR department and oversees all HR functions.',
                'department_id' => $departments->where('code', 'HR')->first()->id,
                'min_salary' => 70000,
                'max_salary' => 90000,
            ],
            [
                'title' => 'HR Specialist',
                'description' => 'Handles specific HR functions such as recruitment or benefits.',
                'department_id' => $departments->where('code', 'HR')->first()->id,
                'min_salary' => 45000,
                'max_salary' => 65000,
            ],
            [
                'title' => 'HR Assistant',
                'description' => 'Provides administrative support to the HR department.',
                'department_id' => $departments->where('code', 'HR')->first()->id,
                'min_salary' => 35000,
                'max_salary' => 45000,
            ],
            
            // fronend positions
            [
                'title' => 'frontend Manager',
                'description' => 'Manages the frontend department and oversees all the frontend functions.',
                'department_id' => $departments->where('code', 'front')->first()->id,
                'min_salary' => 80000,
                'max_salary' => 100000,
            ],
            [
                'title' => 'vuejs Developer',
                'description' => 'Develops and maintains software applications using vuejs.',
                'department_id' => $departments->where('code', 'front')->first()->id,
                'min_salary' => 60000,
                'max_salary' => 85000,
            ],
            [
                'title' => 'react developer',
                'description' => 'Develops and mainitains software applications using react',
                'department_id' => $departments->where('code', 'front')->first()->id,
                'min_salary' => 55000,
                'max_salary' => 75000,
            ],
            
            // Banck positions
            [
                'title' => 'Backend Manager',
                'description' => 'Manages the backend department and oversees all backend functions.',
                'department_id' => $departments->where('code', 'back')->first()->id,
                'min_salary' => 75000,
                'max_salary' => 95000,
            ],
            [
                'title' => 'laravel developer',
                'description' => 'develops and maintains software applications using laravel.',
                'department_id' => $departments->where('code', 'back')->first()->id,
                'min_salary' => 50000,
                'max_salary' => 70000,
            ],
            [
                'title' => 'nodejs developer',
                'description' => ' develops and maintains software applications using nodejs.',
                'department_id' => $departments->where('code', 'back')->first()->id,
                'min_salary' => 55000,
                'max_salary' => 75000,
            ],
            
            // Marketing positions
            [
                'title' => 'Marketing Manager',
                'description' => 'Manages the marketing department and oversees all marketing functions.',
                'department_id' => $departments->where('code', 'MKT')->first()->id,
                'min_salary' => 70000,
                'max_salary' => 90000,
            ],
            [
                'title' => 'Marketing Specialist',
                'description' => 'Develops and implements marketing campaigns.',
                'department_id' => $departments->where('code', 'MKT')->first()->id,
                'min_salary' => 45000,
                'max_salary' => 65000,
            ],
            [
                'title' => 'Content Creator',
                'description' => 'Creates content for marketing campaigns.',
                'department_id' => $departments->where('code', 'MKT')->first()->id,
                'min_salary' => 40000,
                'max_salary' => 60000,
            ],
            
            // Design positions
            [
                'title' => 'desgin manager',
                'description' => 'Manages the desgin department and oversees all desgin related functions.',
                'department_id' => $departments->where('code', 'design')->first()->id,
                'min_salary' => 75000,
                'max_salary' => 95000,
            ],
            [
                'title' => 'design Coordinator',
                'description' => 'Coordinates day-to-day operations.',
                'department_id' => $departments->where('code', 'design')->first()->id,
                'min_salary' => 45000,
                'max_salary' => 65000,
            ],
         
            
            // Sales positions
            [
                'title' => 'Sales Manager',
                'description' => 'Manages the sales department and oversees all sales functions.',
                'department_id' => $departments->where('code', 'SLS')->first()->id,
                'min_salary' => 75000,
                'max_salary' => 95000,
            ],
            [
                'title' => 'Sales Representative',
                'description' => 'Sells products and services to customers.',
                'department_id' => $departments->where('code', 'SLS')->first()->id,
                'min_salary' => 40000,
                'max_salary' => 70000,
            ],
            [
                'title' => 'Account Manager',
                'description' => 'Manages relationships with key accounts.',
                'department_id' => $departments->where('code', 'SLS')->first()->id,
                'min_salary' => 55000,
                'max_salary' => 80000,
            ],
        ];

        foreach ($positions as $positionData) {
            Position::create($positionData);
        }
    }
}

