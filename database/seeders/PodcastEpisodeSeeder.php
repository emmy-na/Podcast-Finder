<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Podcast;
use App\Models\Episode;

class PodcastEpisodeSeeder extends Seeder
{
    /**
     * Run the podcast and episode seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();
        
        // Create podcasts for each user
        foreach ($users as $user) {
            // Create 2-3 podcasts for each user
            $podcasts = Podcast::factory()->count(rand(2, 3))->create([
                'user_id' => $user->id
            ]);
            
            // Create episodes for each podcast
            foreach ($podcasts as $podcast) {
                // Create 3-5 episodes for each podcast
                Episode::factory()->count(rand(3, 5))->create([
                    'podcast_id' => $podcast->id
                ]);
            }
        }
        
        // Create some additional podcasts with specific data
        $adminUser = User::where('role', 'Administrateur')->first();
        if ($adminUser) {
            Podcast::factory()->count(2)->create([
                'user_id' => $adminUser->id,
                'title' => 'Admin Podcast ' . fake()->word(),
                'description' => 'This is an admin podcast with special content'
            ]);
        }
    }
}