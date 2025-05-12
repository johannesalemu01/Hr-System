<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition()
    {
        $start = $this->faker->dateTimeBetween('now', '+1 month');
        $end = (clone $start)->modify('+'.rand(0, 5).' days');

        return [
            'employee_id' => Employee::factory(),
            'leave_type_id' => LeaveType::factory(),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'total_days' => rand(1, 5),
            'reason' => $this->faker->sentence,
            'status' => 'pending',
            'rejection_reason' => null,
        ];
    }
}
