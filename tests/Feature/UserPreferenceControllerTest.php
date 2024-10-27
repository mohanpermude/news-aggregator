<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserPreferenceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a user for testing
        $this->user = User::factory()->create();
    }

    public function test_user_can_store_preferences()
    {
        $data = [
            'preferred_sources' => ['Tech News', 'Eco Times'],
            'preferred_categories' => ['technology', 'environment'],
            'preferred_authors' => ['John Doe', 'Jane Smith'],
        ];

        // Simulate user authentication
        $this->actingAs($this->user);

        $response = $this->postJson('/api/preferences', $data);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'user_id',
                     'preferred_sources',
                     'preferred_categories',
                     'preferred_authors',
                     'created_at',
                     'updated_at',
                 ]);

        // Verify that the preferences were stored in the database
        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $this->user->id,
            'preferred_sources' => json_encode($data['preferred_sources']),
            'preferred_categories' => json_encode($data['preferred_categories']),
            'preferred_authors' => json_encode($data['preferred_authors']),
        ]);
    }

    public function test_user_cannot_store_preferences_without_authentication()
    {
        $data = [
            'preferred_sources' => ['Tech News'],
            'preferred_categories' => ['technology'],
            'preferred_authors' => ['John Doe'],
        ];

        $response = $this->postJson('/api/preferences', $data);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_store_preferences_validation_fails()
    {
        // Simulate user authentication
        $this->actingAs($this->user);

        // Send invalid data (e.g., missing the preferred_sources field)
        $data = [
            'preferred_sources' => 'invalid_data', // should be an array
            'preferred_categories' => null,
            'preferred_authors' => null,
        ];

        $response = $this->postJson('/api/preferences', $data);
        $response->assertStatus(400)
        ->assertJson([
            'preferred_sources' => [
                'The preferred sources must be an array.'
            ]
        ]);
    }

    public function test_user_can_show_preferences()
    {
        // Create a user preference
        UserPreference::create([
            'user_id' => $this->user->id,
            'preferred_sources' => json_encode(['Tech News']),
            'preferred_categories' => json_encode(['technology']),
            'preferred_authors' => json_encode(['John Doe']),
        ]);

        // Simulate user authentication
        $this->actingAs($this->user);

        $response = $this->getJson('/api/preferences');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'user_id',
                     'preferred_sources',
                     'preferred_categories',
                     'preferred_authors',
                     'created_at',
                     'updated_at',
                 ]);
    }

    public function test_user_cannot_show_preferences_without_authentication()
    {
        $response = $this->getJson('/api/preferences');

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_user_can_fetch_personalized_feed()
    {
        // Create user preferences
        UserPreference::create([
            'user_id' => $this->user->id,
            'preferred_sources' => json_encode(['Tech News']),
            'preferred_categories' => json_encode(['technology']),
            'preferred_authors' => json_encode(['John Doe']),
        ]);

        // Create articles
        Article::factory()->create([
            'title' => 'Tech Advances in 2024',
            'published_at' => '2024-10-27',
            'category' => 'technology',
            'author' => 'test',
            'source' => 'Tech News',
        ]);
        Article::factory()->create([
            'title' => 'New Environmental Regulations',
            'published_at' => '2024-10-25',
            'category' => 'environment',
            'author' => 'test',
            'source' => 'Eco Times',
        ]);

        // Simulate user authentication
        $this->actingAs($this->user);

        $response = $this->getJson('/api/preferences/personalized-feed');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'current_page',
                     'data' => [
                         '*' => [
                             'title',
                             'source',
                             'category',
                         ]
                     ],
                     'total',
                     'per_page',
                     'last_page',
                     'first_page_url',
                     'last_page_url',
                     'next_page_url',
                     'prev_page_url',
                 ])
                 ->assertJsonCount(1, 'data'); // Should return 1 article
    }

    public function test_user_cannot_fetch_personalized_feed_without_authentication()
    {
        $response = $this->getJson('/api/preferences/personalized-feed');

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated.']);
    }
}
