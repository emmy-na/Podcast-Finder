<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Animateur 1',
            'email' => 'animateur@example.com',
            'password' => Hash::make('password'),
            'role' => 'animateur',
        ]);

        User::create([
            'name' => 'Utilisateur 1',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'utilisateur',
        ]);
    }
}
