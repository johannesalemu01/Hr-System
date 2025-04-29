<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('events')->insert([
            [
                'title' => 'Payroll Processing',
                'event_date' => Carbon::now()->addDays(3),
                'type' => 'payroll',
                'description' => 'Process payroll for all employees',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Team Meeting',
                'event_date' => Carbon::now()->addDays(5),
                'type' => 'meeting',
                'description' => 'Discuss project progress and upcoming tasks',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'New Employee Onboarding',
                'event_date' => Carbon::now()->addDays(7),
                'type' => 'onboarding',
                'description' => 'Welcome and train new team members',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
