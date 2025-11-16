<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Podcast;
use App\Models\Episode;
use Mockery;

class EpisodeControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $admin;
    protected $animateur;
    protected $podcast;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create users with different roles
        $this->user = User::factory()->create(['role' => 'Utilisateur']);
        $this->animateur = User::factory()->create(['role' => 'Animateur']);
        $this->admin = User::factory()->create(['role' => 'Administrateur']);
        
        // Create a podcast owned by the animateur
        $this->podcast = Podcast::factory()->create(['user_id' => $this->animateur->id]);
    }

    /** @test */
    public function it_can_list_episodes_by_podcast()
    {
        // Create some episodes for the podcast
        Episode::factory()->count(3)->create(['podcast_id' => $this->podcast->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/podcasts/{$this->podcast->id}/episodes");

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function it_can_show_an_episode()
    {
        $episode = Episode::factory()->create(['podcast_id' => $this->podcast->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/episodes/{$episode->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $episode->id,
                'title' => $episode->title
            ]);
    }

    /** @test */
    public function it_can_create_an_episode_as_podcast_owner()
    {
        $data = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
        ];

        $response = $this->actingAs($this->animateur, 'sanctum')
            ->postJson("/api/podcasts/{$this->podcast->id}/episodes", $data);

        $response->assertStatus(201)
            ->assertJson([
                'title' => $data['title'],
                'podcast_id' => $this->podcast->id
            ]);

        $this->assertDatabaseHas('episodes', [
            'title' => $data['title'],
            'podcast_id' => $this->podcast->id
        ]);
    }

    /** @test */
    public function it_cannot_create_an_episode_as_regular_user()
    {
        $data = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/podcasts/{$this->podcast->id}/episodes", $data);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_requires_validation_when_creating_episode()
    {
        $response = $this->actingAs($this->animateur, 'sanctum')
            ->postJson("/api/podcasts/{$this->podcast->id}/episodes", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}