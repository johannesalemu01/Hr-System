<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */    protected static ?string $password;
    public function run(): void
    {
        User::create([  
       'name' => 'admin',
        'email' => 'admin@gmail.com',
        'email_verified_at' => now(),
        'password' => static::$password ??= Hash::make('password'),
    ])->assignRole('writer', 'admin');    
    
    User::create([
        'name'=> 'user',
        'email'=> 'user@gmail.com',
        'email_verified_at'=> now(),
        'password'=> static::$password ??= Hash::make('password'),
    ])->assignRole('read', 'user');
    }
}
