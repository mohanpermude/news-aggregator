<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Creating test articles for testing
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
    }

    /** @test */
    public function it_fetches_articles_with_pagination()
    {
        $response = $this->getJson('/api/articles');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'links', 'meta'])
                 ->assertJsonCount(2, 'data'); // Assumes there are 2 articles created in setUp
    }

    /** @test */
    public function it_filters_articles_by_keyword()
    {
        $response = $this->getJson('/api/articles?keyword=Tech');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.title', 'Tech Advances in 2024');
    }

    /** @test */
    public function it_filters_articles_by_date()
    {
        $response = $this->getJson('/api/articles?date=2024-10-25');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.title', 'New Environmental Regulations');
    }

    /** @test */
    public function it_filters_articles_by_category()
    {
        $response = $this->getJson('/api/articles?category=technology');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.title', 'Tech Advances in 2024');
    }

    /** @test */
    public function it_filters_articles_by_source()
    {
        $response = $this->getJson('/api/articles?source=Tech News');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.title', 'Tech Advances in 2024');
    }

    /** @test */
    public function it_fetches_a_single_article()
    {
        $article = Article::first();

        $response = $this->getJson("/api/articles/{$article->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $article->id,
                     'title' => $article->title,
                     'category' => $article->category,
                     'author' => $article->author,
                     'source' => $article->source,
                 ]);
    }
}
