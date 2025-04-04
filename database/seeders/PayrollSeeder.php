<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payroll;
use App\Models\User;
use Faker\Factory as Faker;

class PayrollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get HR admin for creating and approving payrolls
        $hrAdmin = User::role('hr-admin')->first();
        
        // Create payrolls for the past 6 months
        for ($i = 6; $i >= 0; $i--) {
            // Calculate start and end dates for the month
            $startDate = date('Y-m-01', strtotime("-$i months"));
            $endDate = date('Y-m-t', strtotime("-$i months"));
            $paymentDate = date('Y-m-d', strtotime($endDate . ' +5 days'));
            
            // Generate payroll reference (e.g., PAY-202301)
            $payrollReference = 'PAY-' . date('Ym', strtotime($startDate));
            
            // Determine status based on date
            $status = 'paid';
            if ($i === 0) {
                $status = $faker->randomElement(['processing', 'approved', 'paid']);
            }
            
            // Create payroll
            Payroll::create([
                'payroll_reference' => $payrollReference,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'payment_date' => $paymentDate,
                'status' => $status,
                'notes' => $faker->optional(0.3)->sentence,
                'created_by' => $hrAdmin->id,
                'approved_by' => $status === 'processing' ? null : $hrAdmin->id,
                'approved_at' => $status === 'processing' ? null : date('Y-m-d H:i:s', strtotime($endDate . ' +2 days')),
            ]);
        }
    }
}

