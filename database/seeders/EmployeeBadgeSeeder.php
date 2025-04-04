<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeBadge;
use App\Models\Employee;
use App\Models\Badge;
use App\Models\User;
use Faker\Factory as Faker;

class EmployeeBadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all employees
        $employees = Employee::all();
        
        // Get all badges
        $badges = Badge::all();
        
        // Get managers for awarding badges
        $managers = User::role('manager')->get();
        
        // Award badges to employees
        foreach ($employees as $employee) {
            // Determine number of badges to award (0-3)
            $numBadges = $faker->numberBetween(0, 3);
            
            // Skip if no badges to award
            if ($numBadges === 0) {
                continue;
            }
            
            // Get random badges
            $awardedBadges = $faker->randomElements($badges->toArray(), $numBadges);
            
            foreach ($awardedBadges as $badge) {
                EmployeeBadge::create([
                    'employee_id' => $employee->id,
                    'badge_id' => $badge['id'],
                    'awarded_date' => $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
                    'achievement_details' => $faker->sentence,
                    'awarded_by' => $managers->random()->id,
                ]);
            }
        }
    }
}

