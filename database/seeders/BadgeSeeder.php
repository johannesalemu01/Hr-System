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
                'name' => 'Innovation Champion',
                'description' => 'Awarded to employees who contribute innovative ideas',
                'icon' => 'lightbulb', // Keep LightBulbIcon
                'color' => 'purple',
                'points_required' => 90,
                'badge_type' => 'achievement',
                'is_active' => true,
            ],
            [
                'name' => 'Customer Service Excellence',
                'description' => 'Awarded to employees who provide exceptional customer service',
                'icon' => 'star', // Keep StarIcon
                'color' => 'green',
                'points_required' => 80,
                'badge_type' => 'customer_service',
                'is_active' => true,
            ],
            [
                'name' => 'Leadership Award',
                'description' => 'Awarded to employees who demonstrate exceptional leadership',
                'icon' => 'star', // Changed from 'building-library'/'crown' to 'star'
                'color' => 'red',
                'points_required' => 120,
                'badge_type' => 'leadership',
                'is_active' => true,
            ],
            [
                'name' => 'Perfect Attendance',
                'description' => 'Awarded to employees with perfect attendance',
                'icon' => 'calendar', // Keep CalendarIcon
                'color' => 'teal',
                'points_required' => 50,
                'badge_type' => 'attendance',
                'is_active' => true,
            ],
            [
                'name' => 'Quality Champion',
                'description' => 'Awarded to employees who maintain high quality standards',
                'icon' => 'check-circle', // Keep CheckCircleIcon
                'color' => 'indigo',
                'points_required' => 85,
                'badge_type' => 'teamwork',
                'is_active' => true,
            ],
            [
                'name' => 'Rookie of the Year',
                'description' => 'Awarded to new employees who demonstrate exceptional performance',
                'icon' => 'badge-check', // Keep BadgeCheckIcon
                'color' => 'orange',
                'points_required' => 70,
                'badge_type' => 'attendance',
                'is_active' => true,
            ],
            [
                'name' => 'Sales Champion',
                'description' => 'Awarded to employees who exceed sales targets',
                'icon' => 'chart-bar', // Changed from 'chart-bar-square'/'chart-line' to 'chart-bar'
                'color' => 'emerald',
                'points_required' => 95,
                'badge_type' => 'sales',
                'is_active' => true,
            ],
            [
                'name' => 'Training Completion',
                'description' => 'Awarded to employees who complete all required training',
                'icon' => 'check-circle', // Changed from 'academic-cap'/'graduation-cap' to 'check-circle'
                'color' => 'amber',
                'points_required' => 60,
                'badge_type' => 'leadership',
                'is_active' => true,
            ],
        ];
        
        foreach ($badges as $badgeData) {
            Badge::updateOrCreate(['name' => $badgeData['name']], $badgeData);
        }
    }
}

