<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            [
                'name' => 'Top Performer',
                'description' => 'Awarded to employees who consistently exceed their KPI targets',
                'icon' => 'trophy',
                'color' => 'gold',
                'points_required' => 100,
                'badge_type' => 'achievement',
                'is_active' => true,
            ],
            [
                'name' => 'Team Player',
                'description' => 'Awarded to employees who demonstrate exceptional teamwork',
                'icon' => 'users',
                'color' => 'blue',
                'points_required' => 75,
                'badge_type' => 'teamwork',
                'is_active' => true,
            ],
            [
                'name' => 'Innovation Champion',
                'description' => 'Awarded to employees who contribute innovative ideas',
                'icon' => 'lightbulb',
                'color' => 'purple',
                'points_required' => 90,
                'badge_type' => 'innovation',
                'is_active' => true,
            ],
            [
                'name' => 'Customer Service Excellence',
                'description' => 'Awarded to employees who provide exceptional customer service',
                'icon' => 'star',
                'color' => 'green',
                'points_required' => 80,
                'badge_type' => 'customer_service',
                'is_active' => true,
            ],
            [
                'name' => 'Leadership Award',
                'description' => 'Awarded to employees who demonstrate exceptional leadership',
                'icon' => 'crown',
                'color' => 'red',
                'points_required' => 120,
                'badge_type' => 'leadership',
                'is_active' => true,
            ],
            [
                'name' => 'Perfect Attendance',
                'description' => 'Awarded to employees with perfect attendance',
                'icon' => 'calendar-check',
                'color' => 'teal',
                'points_required' => 50,
                'badge_type' => 'attendance',
                'is_active' => true,
            ],
            [
                'name' => 'Quality Champion',
                'description' => 'Awarded to employees who maintain high quality standards',
                'icon' => 'check-circle',
                'color' => 'indigo',
                'points_required' => 85,
                'badge_type' => 'quality',
                'is_active' => true,
            ],
            [
                'name' => 'Rookie of the Year',
                'description' => 'Awarded to new employees who demonstrate exceptional performance',
                'icon' => 'award',
                'color' => 'orange',
                'points_required' => 70,
                'badge_type' => 'new_employee',
                'is_active' => true,
            ],
            [
                'name' => 'Sales Champion',
                'description' => 'Awarded to employees who exceed sales targets',
                'icon' => 'chart-line',
                'color' => 'emerald',
                'points_required' => 95,
                'badge_type' => 'sales',
                'is_active' => true,
            ],
            [
                'name' => 'Training Completion',
                'description' => 'Awarded to employees who complete all required training',
                'icon' => 'graduation-cap',
                'color' => 'amber',
                'points_required' => 60,
                'badge_type' => 'training',
                'is_active' => true,
            ],
        ];
        
        foreach ($badges as $badgeData) {
            Badge::create($badgeData);
        }
    }
}

