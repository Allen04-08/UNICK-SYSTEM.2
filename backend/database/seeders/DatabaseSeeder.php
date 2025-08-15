<?php

namespace Database\Seeders;

use App\Models\Stage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default users
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => Hash::make('password'), 'role' => 'admin']
        );

        User::updateOrCreate(
            ['email' => 'staff@example.com'],
            ['name' => 'Staff', 'password' => Hash::make('password'), 'role' => 'staff']
        );

        User::updateOrCreate(
            ['email' => 'customer@example.com'],
            ['name' => 'Customer', 'password' => Hash::make('password'), 'role' => 'customer']
        );

        // Default stages
        $stages = [
            ['name' => 'Material Preparation', 'sequence' => 1],
            ['name' => 'Assembly', 'sequence' => 2],
            ['name' => 'Finishing', 'sequence' => 3],
            ['name' => 'Quality Control', 'sequence' => 4],
        ];
        foreach ($stages as $s) {
            Stage::updateOrCreate(['name' => $s['name']], ['sequence' => $s['sequence']]);
        }
    }
}
