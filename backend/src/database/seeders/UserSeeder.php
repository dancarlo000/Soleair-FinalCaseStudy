<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create a Primary Admin User
        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // You can log in with 'password'
            'is_admin' => true,
            'address' => '123 Admin Lane, Metropolis',
        ]);

        // 2. Create a Regular Customer
        User::create([
            'name' => 'Test Customer',
            'username' => 'customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'), // You can log in with 'password'
            'is_admin' => false,
            'address' => '456 Commerce St, Shoetown',
        ]);

        // 3. Create 10 additional fake customers using the factory
        User::factory()->count(10)->create();
    }
}