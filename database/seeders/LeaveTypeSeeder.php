<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'description' => 'Regular paid time off for vacation or personal matters',
                'days_allowed' => 20,
                'requires_approval' => true,
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Sick Leave',
                'description' => 'Leave due to illness or medical appointments',
                'days_allowed' => 10,
                'requires_approval' => true,
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Maternity Leave',
                'description' => 'Leave for female employees before and after childbirth',
                'days_allowed' => 90,
                'requires_approval' => true,
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Paternity Leave',
                'description' => 'Leave for male employees after the birth of their child',
                'days_allowed' => 10,
                'requires_approval' => true,
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Bereavement Leave',
                'description' => 'Leave due to the death of a family member',
                'days_allowed' => 5,
                'requires_approval' => true,
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Study Leave',
                'description' => 'Leave for educational purposes',
                'days_allowed' => 15,
                'requires_approval' => true,
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Unpaid Leave',
                'description' => 'Leave without pay for personal reasons',
                'days_allowed' => 30,
                'requires_approval' => true,
                'is_paid' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Compensatory Leave',
                'description' => 'Leave granted for working on holidays or weekends',
                'days_allowed' => 10,
                'requires_approval' => true,
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Marriage Leave',
                'description' => 'Leave for employees getting married',
                'days_allowed' => 5,
                'requires_approval' => true,
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Work From Home',
                'description' => 'Working remotely from home',
                'days_allowed' => 30,
                'requires_approval' => true,
                'is_paid' => true,
                'is_active' => true,
            ],
        ];
        
        foreach ($leaveTypes as $leaveTypeData) {
            LeaveType::create($leaveTypeData);
        }
    }
}

