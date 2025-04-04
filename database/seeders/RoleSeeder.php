<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if the 'admin' role exists before creating it
        // if (!Role::where('name', 'admin2')->exists()) {
            Role::create(['name' => 'admin2']);
        // }

        // Check if the 'user' role exists before creating it
        // if (!Role::where('name', 'user')->exists()) {
            Role::create(['name' => 'user']);
        // }
    }
} 