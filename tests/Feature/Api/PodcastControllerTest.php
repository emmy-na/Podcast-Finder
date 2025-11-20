<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Podcast;

class PodcastControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $admin;
    protected $animateur;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create users with different roles
        $this->user = User::factory()->create(['role' => 'Utilisateur']);
        $this->animateur = User::factory()->create(['role' => 'Animateur']);
        $this->admin = User::factory()->create(['role' => 'Administrateur']);
    }

    /** @test */
    public function it_can_list_podcasts()
    {
        // Create some podcasts
        Podcast::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/podcasts');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function it_can_show_a_podcast()
    {
        $podcast = Podcast::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/podcasts/{$podcast->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $podcast->id,
                'title' => $podcast->title
            ]);
    }

    /** @test */
    public function it_can_create_a_podcast_as_animateur()
    {
        $data = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
        ];

        $response = $this->actingAs($this->animateur, 'sanctum')
            ->postJson('/api/podcasts', $data);

        $response->assertStatus(201)
            ->assertJson([
                'title' => $data['title'],
                'user_id' => $this->animateur->id
            ]);

        $this->assertDatabaseHas('podcasts', [
            'title' => $data['title'],
            'user_id' => $this->animateur->id
        ]);
    }

    /** @test */
    public function it_cannot_create_a_podcast_as_regular_user()
    {
        $data = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/podcasts', $data);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_requires_validation_when_creating_podcast()
    {
        $response = $this->actingAs($this->animateur, 'sanctum')
            ->postJson('/api/podcasts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }
}