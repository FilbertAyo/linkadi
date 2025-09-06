<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{

public function run(): void
{
    // Create admin user
    $admin = User::factory()->create([
        'name' => 'Test User',
        'email' => 'admin@example.com',
        'password' => Hash::make('admin123'), // password
    ]);

    // Run roles & permissions first
    $this->call(RolesAndPermissionsSeeder::class);

    // Assign role after seeding roles
    $admin->assignRole('admin');
}

}
