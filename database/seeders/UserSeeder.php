<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super-admin');

        // Create HR admin
        $hrAdmin = User::create([
            'name' => 'HR Admin',
            'email' => 'hr@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $hrAdmin->assignRole('hr-admin');

        // Create managers
        $managers = [
            [
                'name' => 'John Manager',
                'email' => 'john.manager@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Jane Manager',
                'email' => 'jane.manager@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Robert Manager',
                'email' => 'robert.manager@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        ];

        foreach ($managers as $managerData) {
            $manager = User::create($managerData);
            $manager->assignRole('manager');
        }

        // Create regular employees (20)
        for ($i = 1; $i <= 20; $i++) {
            $employee = User::create([
                'name' => "Employee $i",
                'email' => "employee$i@example.com",
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $employee->assignRole('employee');
        }
    }
}

