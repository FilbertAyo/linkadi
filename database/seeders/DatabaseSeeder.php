<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call([
            RolePermissionSeeder::class,
            PackageSeeder::class,
        ]);

        // User::factory(10)->create();

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Soscom technologies',
            'email' => 'soscomtechnologies@gmail.com',
            'password' => Hash::make('Soscom@2026!!'),
        ]);
        $admin->assignRole('admin');

        // Create client user
        $client = User::factory()->create([
            'name' => 'Client User',
            'email' => 'client00@example.com',
            'password' => Hash::make('password'),
        ]);
        $client->assignRole('client');
    }
}


