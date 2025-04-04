<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Faker\Factory as Faker;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all employees
        $employees = Employee::all();
        
        // Get all approved leave requests
        $leaveRequests = LeaveRequest::where('status', 'approved')->get();
        
        // Create attendance records for the past 30 days
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $endDate = date('Y-m-d');
        
        $currentDate = $startDate;
        while (strtotime($currentDate) <= strtotime($endDate)) {
            // Skip weekends
            $dayOfWeek = date('N', strtotime($currentDate));
            if ($dayOfWeek >= 6) { // 6 = Saturday, 7 = Sunday
                $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                continue;
            }
            
            foreach ($employees as $employee) {
                // Skip if employee was hired after this date
                if (strtotime($employee->hire_date) > strtotime($currentDate)) {
                    continue;
                }
                
                // Skip if employee was terminated before this date
                if ($employee->termination_date && strtotime($employee->termination_date) < strtotime($currentDate)) {
                    continue;
                }
                
                // Check if employee is on approved leave for this date
                $onLeave = $leaveRequests->filter(function ($leaveRequest) use ($currentDate, $employee) {
                    return $leaveRequest->employee_id === $employee->id &&
                        strtotime($leaveRequest->start_date) <= strtotime($currentDate) &&
                        strtotime($leaveRequest->end_date) >= strtotime($currentDate);
                })->count() > 0;
                
                if ($onLeave) {
                    // Employee is on leave, no attendance record
                    continue;
                }
                
                // Determine attendance status
                $status = $faker->randomElement(['present', 'present', 'present', 'present', 'late', 'absent']); // 67% present, 17% late, 17% absent
                
                // Determine clock in and out times
                $clockIn = null;
                $clockOut = null;
                $hoursWorked = null;
                
                if ($status === 'present' || $status === 'late') {
                    // Regular work hours: 9:00 AM - 5:00 PM
                    if ($status === 'present') {
                        // Clock in between 8:45 AM and 9:15 AM
                        $clockIn = $currentDate . ' ' . $faker->dateTimeBetween('08:45:00', '09:15:00')->format('H:i:s');
                    } else {
                        // Late: Clock in between 9:15 AM and 10:30 AM
                        $clockIn = $currentDate . ' ' . $faker->dateTimeBetween('09:15:01', '10:30:00')->format('H:i:s');
                    }
                    
                    // Clock out between 4:45 PM and 6:00 PM
                    $clockOut = $currentDate . ' ' . $faker->dateTimeBetween('16:45:00', '18:00:00')->format('H:i:s');
                    
                    // Calculate hours worked
                    $clockInTime = new \DateTime($clockIn);
                    $clockOutTime = new \DateTime($clockOut);
                    $interval = $clockInTime->diff($clockOutTime);
                    $hoursWorked = $interval->h + ($interval->i / 60);
                }
                
                // Create attendance record
                Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $currentDate,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'hours_worked' => $hoursWorked,
                    'status' => $status,
                    'notes' => $status === 'absent' ? $faker->randomElement(['Sick', 'Personal emergency', 'Transportation issues', 'Family emergency', null]) : null,
                    'ip_address' => $faker->ipv4,
                    'location' => $faker->optional(0.3)->city,
                ]);
            }
            
            // Move to next day
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }
    }
}

