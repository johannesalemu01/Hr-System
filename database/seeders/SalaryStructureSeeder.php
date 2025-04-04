<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalaryStructure;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Faker\Factory as Faker;

class SalaryStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all employees
        $employees = Employee::all();
        
        // Get HR admin for creating salary structures
        $hrAdmin = User::role('hr-admin')->first();
        
        // Create salary structures for employees
        foreach ($employees as $employee) {
            // Get position for salary range
            $position = Position::find($employee->position_id);
            
            // Calculate basic salary within position range
            $basicSalary = $faker->numberBetween($position->min_salary, $position->max_salary);
            
            // Calculate allowances as percentages of basic salary
            $housingAllowance = $basicSalary * $faker->randomFloat(2, 0.1, 0.2);
            $transportAllowance = $basicSalary * $faker->randomFloat(2, 0.05, 0.1);
            $mealAllowance = $basicSalary * $faker->randomFloat(2, 0.03, 0.07);
            $medicalAllowance = $basicSalary * $faker->randomFloat(2, 0.05, 0.1);
            $otherAllowances = $basicSalary * $faker->randomFloat(2, 0, 0.05);
            
            // Create current salary structure
            SalaryStructure::create([
                'employee_id' => $employee->id,
                'basic_salary' => $basicSalary,
                'housing_allowance' => $housingAllowance,
                'transport_allowance' => $transportAllowance,
                'meal_allowance' => $mealAllowance,
                'medical_allowance' => $medicalAllowance,
                'other_allowances' => $otherAllowances,
                'other_allowances_description' => $faker->optional(0.3)->sentence,
                'effective_date' => $employee->hire_date,
                'end_date' => null,
                'is_current' => true,
                'notes' => $faker->optional(0.3)->sentence,
                'created_by' => $hrAdmin->id,
            ]);
            
            // 30% chance to have a previous salary structure
            if ($faker->boolean(30) && strtotime($employee->hire_date) < strtotime('-1 year')) {
                // Calculate previous basic salary (lower than current)
                $previousBasicSalary = $basicSalary * $faker->randomFloat(2, 0.85, 0.95);
                
                // Calculate previous allowances
                $previousHousingAllowance = $previousBasicSalary * $faker->randomFloat(2, 0.1, 0.2);
                $previousTransportAllowance = $previousBasicSalary * $faker->randomFloat(2, 0.05, 0.1);
                $previousMealAllowance = $previousBasicSalary * $faker->randomFloat(2, 0.03, 0.07);
                $previousMedicalAllowance = $previousBasicSalary * $faker->randomFloat(2, 0.05, 0.1);
                $previousOtherAllowances = $previousBasicSalary * $faker->randomFloat(2, 0, 0.05);
                
                // Calculate effective and end dates
                $effectiveDate = date('Y-m-d', strtotime($employee->hire_date));
                $endDate = date('Y-m-d', strtotime($effectiveDate . ' +1 year -1 day'));
                
                // Create previous salary structure
                SalaryStructure::create([
                    'employee_id' => $employee->id,
                    'basic_salary' => $previousBasicSalary,
                    'housing_allowance' => $previousHousingAllowance,
                    'transport_allowance' => $previousTransportAllowance,
                    'meal_allowance' => $previousMealAllowance,
                    'medical_allowance' => $previousMedicalAllowance,
                    'other_allowances' => $previousOtherAllowances,
                    'other_allowances_description' => $faker->optional(0.3)->sentence,
                    'effective_date' => $effectiveDate,
                    'end_date' => $endDate,
                    'is_current' => false,
                    'notes' => 'Previous salary structure',
                    'created_by' => $hrAdmin->id,
                ]);
            }
        }
    }
}

