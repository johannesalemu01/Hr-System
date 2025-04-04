<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\User;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $managers = User::role('manager')->get();
        
        $departments = [
            [
                'name' => 'Human Resources',
                'code' => 'HR',
                'description' => 'Responsible for recruiting, hiring, and managing employee benefits.',
                'manager_id' => $managers[0]->id,
            ],
            [
                'name' => 'Information Technology',
                'code' => 'IT',
                'description' => 'Responsible for managing and maintaining the company\'s technology infrastructure.',
                'manager_id' => $managers[1]->id,
            ],
            [
                'name' => 'Finance',
                'code' => 'FIN',
                'description' => 'Responsible for managing the company\'s financial resources and reporting.',
                'manager_id' => $managers[2]->id,
            ],
            [
                'name' => 'Marketing',
                'code' => 'MKT',
                'description' => 'Responsible for promoting the company\'s products and services.',
                'manager_id' => $managers[0]->id,
            ],
            [
                'name' => 'Operations',
                'code' => 'OPS',
                'description' => 'Responsible for the day-to-day operations of the company.',
                'manager_id' => $managers[1]->id,
            ],
            [
                'name' => 'Sales',
                'code' => 'SLS',
                'description' => 'Responsible for selling the company\'s products and services.',
                'manager_id' => $managers[2]->id,
            ],
        ];

        foreach ($departments as $departmentData) {
            Department::create($departmentData);
        }
    }
}

