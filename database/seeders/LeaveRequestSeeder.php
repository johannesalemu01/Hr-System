<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\User;
use Faker\Factory as Faker;

class LeaveRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all employees
        $employees = Employee::all();
        
        // Get all leave types
        $leaveTypes = LeaveType::all();
        
        // Get managers for approving leave requests
        $managers = User::role('manager')->get();
        
        // Create leave requests for employees
        foreach ($employees as $employee) {
            // Skip if employee was hired less than 3 months ago
            if (strtotime($employee->hire_date) > strtotime('-3 months')) {
                continue;
            }
            
            // Determine number of leave requests (1-5)
            $numRequests = $faker->numberBetween(1, 5);
            
            for ($i = 0; $i < $numRequests; $i++) {
                // Randomly select leave type
                $leaveType = $leaveTypes->random();
                
                // Determine start and end dates
                $startDate = $faker->dateTimeBetween('-6 months', '+2 months')->format('Y-m-d');
                $totalDays = $faker->numberBetween(1, min(5, $leaveType->days_allowed));
                $endDate = date('Y-m-d', strtotime($startDate . " +$totalDays days"));
                
                // Determine status based on dates
                $status = 'pending';
                $approvedBy = null;
                $approvedAt = null;
                $rejectionReason = null;
                
                if (strtotime($startDate) < strtotime('today')) {
                    // Past leave requests are either approved or rejected
                    $status = $faker->randomElement(['approved', 'approved', 'approved', 'rejected']); // 75% chance of approval
                    
                    if ($status === 'approved') {
                        $approvedBy = $managers->random()->id;
                        $approvedAt = $faker->dateTimeBetween($startDate . ' -1 week', $startDate . ' -1 day')->format('Y-m-d H:i:s');
                    } else {
                        $rejectionReason = $faker->randomElement([
                            'Insufficient leave balance',
                            'Critical project deadline',
                            'Too many team members on leave during the same period',
                            'Insufficient notice period',
                            'Business needs require your presence',
                        ]);
                    }
                }
                
                // Create leave request
                LeaveRequest::create([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'total_days' => $totalDays,
                    'reason' => $faker->sentence,
                    'status' => $status,
                    'approved_by' => $approvedBy,
                    'approved_at' => $approvedAt,
                    'rejection_reason' => $rejectionReason,
                    'attachment' => $faker->optional(0.2)->uuid . '.pdf',
                ]);
            }
        }
    }
}

