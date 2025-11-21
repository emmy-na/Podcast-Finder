<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Podcast;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Episode>
 */
class EpisodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(2),
            'description' => fake()->paragraph(),
            'audio_file' => 'https://res.cloudinary.com/demo/video/upload/v1234567890/podcast_episodes/episode_' . fake()->numberBetween(1, 100) . '.mp3',
            'podcast_id' => Podcast::factory(),
        ];
    }
}