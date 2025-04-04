<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeDocument;
use App\Models\Employee;
use Faker\Factory as Faker;

class EmployeeDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all employees
        $employees = Employee::all();
        
        // Document types
        $documentTypes = [
            'Resume',
            'ID Card',
            'Passport',
            'Driving License',
            'Educational Certificate',
            'Employment Contract',
            'Work Permit',
            'Tax Document',
            'Health Insurance',
            'Performance Review',
        ];
        
        // Create 2-5 documents for each employee
        foreach ($employees as $employee) {
            $numDocuments = $faker->numberBetween(2, 5);
            
            // Shuffle document types and take a subset
            $shuffledTypes = $faker->randomElements($documentTypes, $numDocuments);
            
            foreach ($shuffledTypes as $documentType) {
                $expiryDate = null;
                
                // Some document types have expiry dates
                if (in_array($documentType, ['Passport', 'Driving License', 'Work Permit', 'Health Insurance'])) {
                    $expiryDate = $faker->dateTimeBetween('+6 months', '+5 years')->format('Y-m-d');
                }
                
                EmployeeDocument::create([
                    'employee_id' => $employee->id,
                    'document_type' => $documentType,
                    'title' => $employee->first_name . "'s " . $documentType,
                    'file_path' => 'documents/' . $faker->uuid . '.pdf',
                    'description' => $faker->optional(0.7)->sentence,
                    'expiry_date' => $expiryDate,
                ]);
            }
        }
    }
}

