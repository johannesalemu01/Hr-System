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
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('super-admin');

        
        // Create HR admin
        $hrAdmin = User::firstOrCreate(
            ['email' => 'hr@example.com'],
            [
                'name' => 'HR Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
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
            $manager = User::firstOrCreate(
                ['email' => $managerData['email']],
                $managerData
            );
            $manager->assignRole('manager');
        }

        // Create regular employees (20)
        for ($i = 1; $i <= 20; $i++) {
            $employee = User::firstOrCreate(
                ['email' => "employee$i@example.com"],
                [
                    'name' => "Employee $i",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $employee->assignRole('employee');
        }
    }
}

