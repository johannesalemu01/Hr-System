<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use Faker\Factory as Faker;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all users with employee role
        $users = User::role('employee')->get();
        
        // Get all departments
        $departments = Department::all();
        
        // Get all positions
        $positions = Position::all();
        
        // Get all manager users
        $managers = User::role('manager')->get();
        
        // Create employees for each user
        foreach ($users as $index => $user) {
            // Randomly select department
            $department = $departments->random();
            
            // Get positions for this department
            $departmentPositions = $positions->where('department_id', $department->id);
            
            // Randomly select position from department positions
            $position = $departmentPositions->random();
            
            // Randomly select manager
            $manager = $managers->random();
            
            // Generate employee ID (e.g., EMP001, EMP002, etc.)
            $employeeId = 'EMP' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            
            // Generate random gender
            $gender = $faker->randomElement(['male', 'female']);
            
            // Generate random marital status
            $maritalStatus = $faker->randomElement(['single', 'married', 'divorced', 'widowed']);
            
            // Generate random employment status
            $employmentStatus = $faker->randomElement(['full-time', 'part-time', 'contract', 'probation']);
            
            // Create employee
            Employee::create([
                'user_id' => $user->id,
                'employee_id' => $employeeId,
                'first_name' => $faker->firstName($gender),
                'last_name' => $faker->lastName,
                'middle_name' => $faker->optional(0.7)->firstName,
                'date_of_birth' => $faker->dateTimeBetween('-60 years', '-20 years')->format('Y-m-d'),
                'gender' => $gender,
                'marital_status' => $maritalStatus,
                'nationality' => $faker->country,
                'address' => $faker->streetAddress,
                'city' => $faker->city,
                'state' => $faker->state,
                'postal_code' => $faker->postcode,
                'country' => $faker->country,
                'phone_number' => $faker->phoneNumber,
                'emergency_contact_name' => $faker->name,
                'emergency_contact_phone' => $faker->phoneNumber,
                'emergency_contact_relationship' => $faker->randomElement(['spouse', 'parent', 'sibling', 'friend']),
                'department_id' => $department->id,
                'position_id' => $position->id,
                'manager_id' => $manager->id,
                'hire_date' => $faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
                'termination_date' => $faker->optional(0.1)->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
                'employment_status' => $employmentStatus,
                'bank_name' => $faker->company,
                'bank_account_number' => $faker->bankAccountNumber,
                'bank_branch' => $faker->city,
                'tax_id' => $faker->numerify('TAX-####-####-####'),
                'social_security_number' => $faker->numerify('###-##-####'),
                'profile_picture' => null,
            ]);
        }
        
        // Create employees for managers
        foreach ($managers as $index => $manager) {
            // Randomly select department (preferably the one they manage)
            $department = Department::where('manager_id', $manager->id)->first() ?? $departments->random();
            
            // Get manager positions
            $managerPositions = $positions->where('department_id', $department->id)
                ->filter(function ($position) {
                    return str_contains($position->title, 'Manager');
                });
            
            // If no manager position found, get any position from the department
            $position = $managerPositions->count() > 0 
                ? $managerPositions->first() 
                : $positions->where('department_id', $department->id)->first();
            
            // Generate employee ID for manager
            $employeeId = 'MGR' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            
            // Generate random gender
            $gender = $faker->randomElement(['male', 'female']);
            
            // Generate random marital status
            $maritalStatus = $faker->randomElement(['single', 'married', 'divorced', 'widowed']);
            
            // Create employee for manager
            Employee::create([
                'user_id' => $manager->id,
                'employee_id' => $employeeId,
                'first_name' => explode(' ', $manager->name)[0],
                'last_name' => explode(' ', $manager->name)[1] ?? $faker->lastName,
                'middle_name' => $faker->optional(0.5)->firstName,
                'date_of_birth' => $faker->dateTimeBetween('-60 years', '-30 years')->format('Y-m-d'),
                'gender' => $gender,
                'marital_status' => $maritalStatus,
                'nationality' => $faker->country,
                'address' => $faker->streetAddress,
                'city' => $faker->city,
                'state' => $faker->state,
                'postal_code' => $faker->postcode,
                'country' => $faker->country,
                'phone_number' => $faker->phoneNumber,
                'emergency_contact_name' => $faker->name,
                'emergency_contact_phone' => $faker->phoneNumber,
                'emergency_contact_relationship' => $faker->randomElement(['spouse', 'parent', 'sibling', 'friend']),
                'department_id' => $department->id,
                'position_id' => $position->id,
                'manager_id' => null, // Managers don't have managers
                'hire_date' => $faker->dateTimeBetween('-10 years', '-5 years')->format('Y-m-d'),
                'termination_date' => null,
                'employment_status' => 'full-time',
                'bank_name' => $faker->company,
                'bank_account_number' => $faker->bankAccountNumber,
                'bank_branch' => $faker->city,
                'tax_id' => $faker->numerify('TAX-####-####-####'),
                'social_security_number' => $faker->numerify('###-##-####'),
                'profile_picture' => null,
            ]);
        }
    }
}

