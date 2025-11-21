<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create an admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'Administrateur',
        ]);

        // Create an animateur user
        User::create([
            'name' => 'Animateur User',
            'email' => 'animateur@example.com',
            'password' => Hash::make('password'),
            'role' => 'Animateur',
        ]);

        // Create a regular utilisateur
        User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'Utilisateur',
        ]);
    }
}