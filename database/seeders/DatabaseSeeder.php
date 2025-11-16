<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);


         User::factory(3)->create()->each(function($user){
        Podcast::factory(2)->create(['user_id'=>$user->id])->each(function($podcast){
            Episode::factory(3)->create(['podcast_id'=>$podcast->id]);
        });
    });

    User::factory()->create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('admin123'),
        'role' => 'Administrateur',
    ]);
    }
}
