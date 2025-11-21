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
        // Run the user seeder first
        $this->call([
            UserSeeder::class,
        ]);
        
        // Then run the podcast and episode seeder
        $this->call([
            PodcastEpisodeSeeder::class,
        ]);
    }
}