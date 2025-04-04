<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles first
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        // Create admin user with verified email
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin1234'),
            'email_verified_at' => now(),
        ]);

        // Assign admin role
        $admin->assignRole('admin');

        User::factory()->create([
            'name' => 'user User',
            'email' => 'user@gmail.com',
            'password' => bcrypt('user1234'),
        ]);
    }
}








// <?php

// namespace Database\Seeders;

// use Illuminate\Database\Seeder;

// class DatabaseSeeder extends Seeder
// {
//     /**
//      * Seed the application's database.
//      */
//     public function run(): void
//     {
//         $this->call([
//             // Users and Roles
//             RoleAndPermissionSeeder::class,
//             UserSeeder::class,
            
//             // Organization Structure
//             DepartmentSeeder::class,
//             PositionSeeder::class,
            
//             // Employee Management
//             EmployeeSeeder::class,
//             EmployeeDocumentSeeder::class,
            
//             // KPI and Gamification
//             KpiSeeder::class,
//             EmployeeKpiSeeder::class,
//             KpiRecordSeeder::class,
//             BadgeSeeder::class,
//             EmployeeBadgeSeeder::class,
//             PointSeeder::class,
//             LeaderboardSeeder::class,
            
//             // Payroll
//             SalaryStructureSeeder::class,
//             PayrollSeeder::class,
//             PayrollItemSeeder::class,
//             DeductionSeeder::class,
//             BonusSeeder::class,
            
//             // Leave and Attendance
//             LeaveTypeSeeder::class,
//             LeaveRequestSeeder::class,
//             AttendanceSeeder::class,
//         ]);
//     }
// }



  