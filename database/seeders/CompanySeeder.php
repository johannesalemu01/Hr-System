<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    public function run()
    {
        Company::create([
            'name' => 'Default Company',
            'address' => '123 Main Street',
            'phone' => '123-456-7890',
        ]);
    }
}
