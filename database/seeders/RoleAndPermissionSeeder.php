<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // First, let's truncate the tables to start fresh (optional - use with caution in production)
        // Uncomment these lines if you want to completely reset roles and permissions
        // DB::statement('SET FOREIGN_KEY_CHECKS=0');
        // DB::table('role_has_permissions')->truncate();
        // DB::table('model_has_roles')->truncate();
        // DB::table('model_has_permissions')->truncate();
        // DB::table('roles')->truncate();
        // DB::table('permissions')->truncate();
        // DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Create roles
        $roles = ['super-admin','admin', 'hr-admin', 'manager', 'employee'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Create permissions
        $permissions = [
            // User management
            'view users', 'create users', 'edit users', 'delete users',
            
            // Department management
            'view departments', 'create departments', 'edit departments', 'delete departments',
            
            // Position management
            'view positions', 'create positions', 'edit positions', 'delete positions',
            
            // Employee management
            'view employees', 'create employees', 'edit employees', 'delete employees',
            'view employee documents', 'create employee documents', 'edit employee documents', 'delete employee documents',
            
            // KPI management
            'view kpis', 'create kpis', 'edit kpis', 'delete kpis',
            'view employee kpis', 'create employee kpis', 'edit employee kpis', 'delete employee kpis',
            'view kpi records', 'create kpi records', 'edit kpi records', 'delete kpi records',
            
            // Gamification
            'view badges', 'create badges', 'edit badges', 'delete badges',
            'view employee badges', 'create employee badges', 'edit employee badges', 'delete employee badges',
            'view points', 'create points', 'edit points', 'delete points',
            'view leaderboards', 'create leaderboards', 'edit leaderboards', 'delete leaderboards',
            
            // Payroll
            'view salary structures', 'create salary structures', 'edit salary structures', 'delete salary structures',
            'view payrolls', 'create payrolls', 'edit payrolls', 'delete payrolls', 'approve payrolls',
            'view payroll items', 'create payroll items', 'edit payroll items', 'delete payroll items',
            'view deductions', 'create deductions', 'edit deductions', 'delete deductions',
            'view bonuses', 'create bonuses', 'edit bonuses', 'delete bonuses',
            
            // Leave and Attendance
            'view leave types', 'create leave types', 'edit leave types', 'delete leave types',
            'view leave requests', 'create leave requests', 'edit leave requests', 'delete leave requests', 'approve leave requests',
            'view attendances', 'create attendances', 'edit attendances', 'delete attendances',
            
            // Reports
            'view reports', 'create reports', 'export reports',
        ];

        // Create or update permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        Role::findByName('super-admin')->givePermissionTo($permissions);
        Role::findByName('admin')->givePermissionTo($permissions);
        Role::findByName('hr-admin')->givePermissionTo([
            'view users', 'create users', 'edit users',
            'view departments', 'create departments', 'edit departments',
            'view positions', 'create positions', 'edit positions',
            'view employees', 'create employees', 'edit employees', 'delete employees',
            'view employee documents', 'create employee documents', 'edit employee documents', 'delete employee documents',
            'view kpis', 'create kpis', 'edit kpis',
            'view employee kpis', 'create employee kpis', 'edit employee kpis',
            'view kpi records', 'create kpi records', 'edit kpi records',
            'view badges', 'create badges', 'edit badges',
            'view employee badges', 'create employee badges', 'edit employee badges',
            'view points', 'create points', 'edit points',
            'view leaderboards', 'create leaderboards', 'edit leaderboards',
            'view salary structures', 'create salary structures', 'edit salary structures',
            'view payrolls', 'create payrolls', 'edit payrolls', 'approve payrolls',
            'view payroll items', 'create payroll items', 'edit payroll items',
            'view deductions', 'create deductions', 'edit deductions',
            'view bonuses', 'create bonuses', 'edit bonuses',
            'view leave types', 'create leave types', 'edit leave types',
            'view leave requests', 'approve leave requests',
            'view attendances', 'edit attendances',
            'view reports', 'create reports', 'export reports',
        ]);
        Role::findByName('manager')->givePermissionTo([
            'view employees',
            'create employees', // Ensure this permission is included
            'view employee documents',
            'view kpis',
            'view employee kpis', 'create employee kpis', 'edit employee kpis',
            'view kpi records', 'create kpi records', 'edit kpi records',
            'view badges',
            'view employee badges', 'create employee badges',
            'view points', 'create points',
            'view leaderboards',
            'view leave requests', 'approve leave requests',
            'view attendances',
            'view reports',
        ]);
        Role::findByName('employee')->givePermissionTo([
            'view kpis',
            'view employee kpis',
            'view kpi records',
            'view badges',
            'view employee badges',
            'view points',
            'view leaderboards',
            'view leave requests', 'create leave requests', 'edit leave requests',
            'view attendances', 'create attendances',
        ]);
    }
}