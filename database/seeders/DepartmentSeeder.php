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
                'name' => 'FrontEnd',
                'code' => 'front',
                'description' => 'Responsible for developing the user interface and user experience of web applications.',
                'manager_id' => $managers[1]->id,
            ],
            [
                'name' => 'Backend',
                'code' => 'back',
                'description' => 'Responsible for developing the server-side logic and database management of web applications.',
                'manager_id' => $managers[2]->id,
            ],
            [
                'name' => 'Marketing',
                'code' => 'MKT',
                'description' => 'Responsible for promoting the company\'s products and services.',
                'manager_id' => $managers[0]->id,
            ],
            [
                'name' => 'Design',
                'code' => 'design',
                'description' => 'Responsible for creating visual concepts and designs for the company\'s products.',
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
            Department::firstOrCreate(
                ['code'=>$departmentData['code']],
                $departmentData);
        }
    }
}

