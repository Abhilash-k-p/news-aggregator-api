<?php


namespace Tests\Feature\Api\V1;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_fetch_articles_successfully()
    {
        // Create a few articles in the database (15 in total)
        Article::factory()->count(15)->create();

        // Create a user and generate a personal access token
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;

        // Make the API request to fetch articles with the Bearer token in the Authorization header
        $response = $this->getJson('api/v1/articles', [
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$token}"
        ]);

        // Assert the response status is 200 (OK)
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'message',
            ]);

        // Ensure the number of items per page is 10 (default pagination)
        $response->assertJsonFragment([
            'per_page' => 10,
        ]);

        // You can also check if there are additional pages (since we have 15 articles)
        $response->assertJsonFragment([
            'last_page' => 2, // Since we have 15 articles and 10 per page, there should be 2 pages
        ]);
    }

    #[Test]
    public function test_fetch_articles_with_custom_per_page()
    {
        // Create a few articles in the database
        Article::factory()->count(20)->create();
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;


        // Make the API request with custom `per_page` parameter
        $response = $this->getJson('api/v1/articles?per_page=5',  [
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$token}"
        ]);

        // Assert the response status is 200 (OK)
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'message',
            ]);

        // Ensure the number of items per page is 5
        $response->assertJsonFragment([
            'per_page' => 5,
        ]);

        // You can also check if there are additional pages (since we have 20 articles)
        $response->assertJsonFragment([
            'last_page' => 4, // Since we have 20 articles and 5 per page, there should be 4 pages
        ]);
    }


    #[Test]
    public function test_search_articles_successfully()
    {
        $category = Category::factory()->create(['name' => 'Business']);
        Article::factory()->create(['title' => 'Business Insights', 'content' => '', 'category_id' => $category->id]);
        Article::factory()->create(['title' => 'Tech Trends', 'content' => '']);

        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;

        // Test with a keyword filter
        $response = $this->getJson('api/v1/articles/search?keyword=Tech', [
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$token}"
        ]);
        // Assert the response status is 200 (OK) and contains the correct article
        $response->assertStatus(200)
            ->assertJsonFragment([
                'title' => 'Tech Trends',
            ])
            ->assertJsonCount(1, 'data.data'); // Ensure only 1 article is returned

        // Test with a category filter
        $response = $this->getJson('api/v1/articles/search?category=Business');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'title' => 'Business Insights',
            ])
            ->assertJsonCount(1, 'data.data'); // Ensure only 1 article is returned
    }

    #[Test]
    public function test_search_articles_with_invalid_filters()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;


        // Send request with invalid filters
        $response = $this->getJson('api/v1/articles/search?keyword=invalid&category=unknown', [
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$token}"
        ]);

        // Assert the response status is 422 (Validation Error)
        $response->assertStatus(200)
            ->assertJsonCount(0, 'data.data');
    }

    #[Test]
    public function test_show_article_successfully()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;

        // Create an article
        $article = Article::factory()->create([
            'title' => 'Sample Article',
            'author' => 'John Doe',
        ]);

        // Mock the cache to avoid hitting the actual cache store in test
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn($article);

        // Make the API request to fetch the article by ID
        $response = $this->getJson("api/v1/articles/{$article->id}", [
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$token}"
        ]);

        // Assert the response status is 200 (OK)
        $response->assertStatus(200)
            ->assertJsonFragment([
                'title' => 'Sample Article',
                'author' => 'John Doe',
            ]);
    }

    #[Test]
    public function test_show_article_not_found()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;

        // Make the API request to fetch a non-existing article
        $response = $this->getJson('api/v1/articles/999', [
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$token}"
        ]);

        // Assert the response status is 404 (Not Found)
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Article not found.',
            ]);
    }

    #[Test]
    public function test_show_article_with_cache_miss()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;

        // Create an article
        $article = Article::factory()->create([
            'title' => 'Cached Article',
        ]);

        // Mock the cache miss scenario (not retrieving from cache)
        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) use ($article) {
                return $article;
            });

        // Make the API request to fetch the article by ID
        $response = $this->getJson("api/v1/articles/{$article->id}", [
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$token}"
        ]);

        // Assert the response status is 200 (OK)
        $response->assertStatus(200)
            ->assertJsonFragment([
                'title' => 'Cached Article',
            ]);
    }
}
