<?php

namespace Tests\Unit\Services\Source\Vendors;

use App\Models\Source;
use App\Services\Source\Vendors\CurrentsApiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CurrentsApiServiceTest extends TestCase
{
    protected $service;
    protected $mockSource;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock source object
        $this->mockSource = Mockery::mock(Source::class)->makePartial();
        $this->mockSource->base_url = 'https://api.currentsapi.services/v1';
        $this->mockSource->api_key = 'test-api-key';
        $this->mockSource->id = 1;

        // Create service instance
        $this->service = Mockery::mock(CurrentsApiService::class, [$this->mockSource])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    #[Test]
    public function test_fetch_articles_success()
    {
        $categories = collect([
            (object)['name' => 'technology', 'id' => 1],
            (object)['name' => 'sports', 'id' => 2]
        ]);

        $mockApiResponse = [
            'news' => [
                ['id' => '1', 'title' => 'Tech News', 'description' => 'Content here', 'url' => 'http://example.com', 'published' => now(), 'author' => 'Author1'],
                ['id' => '2', 'title' => 'Sports News', 'description' => 'Sports content', 'url' => 'http://example2.com', 'published' => now(), 'author' => 'Author2']
            ]
        ];

        // Mock the `getCategories` method
        $this->service->shouldReceive('getCategories')->andReturn($categories);

        // Mock the `makeRequest` method
        $this->service->shouldReceive('makeRequest')
            ->with('https://api.currentsapi.services/v1/latest-news', Mockery::any())
            ->andReturn($mockApiResponse);

        $result = $this->service->fetchArticles();


        $this->assertCount(2 * 2, $result); // 2 category returns 2 articles - totally 4
        $this->assertEquals('Tech News', $result[0]['title']);
        $this->assertEquals('Sports News', $result[1]['title']);
    }

    #[Test]
    public function test_fetch_articles_logs_error_on_invalid_response()
    {
        $categories = collect([(object)['name' => 'technology', 'id' => 1]]);

        // Mock the `getCategories` method
        $this->service->shouldReceive('getCategories')->andReturn($categories);

        // Mock the `makeRequest` method to return an error
        $this->service->shouldReceive('makeRequest')
            ->andReturn(null);

        // Mock the Log facade
        Log::shouldReceive('error')->once();

        $result = $this->service->fetchArticles();

        $this->assertEmpty($result);
    }

    #[Test]
    public function test_process_articles()
    {
        $mockArticles = [
            ['id' => '1', 'title' => 'Tech News', 'description' => 'Content here', 'url' => 'http://example.com', 'published' => '2024-11-15T10:00:00Z', 'author' => 'Author1'],
            ['id' => '2', 'title' => 'Sports News', 'description' => 'Sports content', 'url' => 'http://example2.com', 'published' => '2024-11-15T12:00:00Z', 'author' => 'Author2']
        ];

        Carbon::setTestNow(Carbon::parse('2024-11-15T10:00:00Z'));

        $categoryId = 1;

        $result = $this->service->processArticles($mockArticles, $categoryId);

        $this->assertCount(2, $result);
        $this->assertEquals('Tech News', $result[0]['title']);
        $this->assertEquals($categoryId, $result[0]['category_id']);
        $this->assertEquals('2024-11-15 10:00:00', $result[0]['published_at']);
    }
}
