<?php

namespace Tests\Unit\Services\Source\Vendors;

use App\Models\Category;
use App\Models\Source;
use App\Services\Source\Vendors\GuardianApiService;
use App\Services\Source\Vendors\NytApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NytApiServiceTest extends TestCase
{


    protected $nytApiService;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock source object
        $this->mockSource = Mockery::mock(Source::class)->makePartial();
        $this->mockSource->base_url = 'https://api.nytimes.com/svc/search/v2';
        $this->mockSource->api_key = 'test-api-key';
        $this->mockSource->id = 3;

        // Mock the BaseApiService dependencies
        $this->nytApiService = Mockery::mock(NytApiService::class, [$this->mockSource])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }


    /**
     * Test fetchArticles when API returns valid data.
     *
     * @return void
     */
    #[Test]
    public function testFetchArticlesSuccessfully()
    {
        $this->nytApiService->shouldReceive('getCategories')
            ->andReturn(collect([
                (object)['name' => 'sports', 'id' => 1],
            ]));

        // Mock makeRequest to simulate a successful API call
        $apiResponse = [
            'response' => [
                'docs' => [
                    [
                        '_id' => '123',
                        'headline' => ['main' => 'Test Article'],
                        'lead_paragraph' => 'Test content',
                        'web_url' => 'https://test.com/article/123',
                        'pub_date' => '2023-11-19T00:00:00Z',
                        'byline' => [
                            'person' => [
                                'firstname' => 'John',
                                'lastname' => 'Doe',
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->nytApiService->shouldReceive('makeRequest')->andReturn($apiResponse);

        // Call fetchArticles method
        $articles = $this->nytApiService->fetchArticles();

        // Assertions
        $this->assertCount(1, $articles);
        $this->assertEquals('Test Article', $articles[0]['title']);
        $this->assertEquals('123', $articles[0]['source_article_id']);
        $this->assertEquals('Test content', $articles[0]['content']);
        $this->assertEquals('https://test.com/article/123', $articles[0]['url']);
        $this->assertEquals(Carbon::parse('2023-11-19T00:00:00Z')->toDateTimeString(), $articles[0]['published_at']);
        $this->assertEquals(1, $articles[0]['category_id']);
    }

    /**
     * Test fetchArticles when API returns invalid data.
     *
     * @return void
     */
    #[Test]
    public function testFetchArticlesHandlesErrors()
    {
        $this->nytApiService->shouldReceive('getCategories')
            ->andReturn(collect([
                (object)['name' => 'sports', 'id' => 1],
            ]));

        // Mock makeRequest to simulate an error response
        $this->nytApiService->shouldReceive('makeRequest')->andReturn(null);

        // Mock the Log facade to expect an error log
        Log::shouldReceive('error')
            ->once();

        // Call fetchArticles method
        $articles = $this->nytApiService->fetchArticles();

        // Assertions
        $this->assertEmpty($articles);
    }

    /**
     * Test processArticles method.
     *
     * @return void
     */
    #[Test]
    public function testProcessArticles()
    {
        // Prepare mock data
        $articleData = [
            [
                '_id' => '123',
                'headline' => ['main' => 'Test Article'],
                'lead_paragraph' => 'Test content',
                'web_url' => 'https://test.com/article/123',
                'pub_date' => '2023-11-19T00:00:00Z',
                'byline' => [
                    'person' => [
                        'firstname' => 'John',
                        'lastname' => 'Doe',
                    ]
                ]
            ]
        ];

        // Call processArticles method
        $processedArticles = $this->nytApiService->processArticles($articleData, 1);

        // Assertions
        $this->assertCount(1, $processedArticles);
        $this->assertEquals('Test Article', $processedArticles[0]['title']);
        $this->assertEquals('123', $processedArticles[0]['source_article_id']);
        $this->assertEquals('Test content', $processedArticles[0]['content']);
        $this->assertEquals('https://test.com/article/123', $processedArticles[0]['url']);
        $this->assertEquals(Carbon::parse('2023-11-19T00:00:00Z')->toDateTimeString(), $processedArticles[0]['published_at']);
        $this->assertEquals(1, $processedArticles[0]['category_id']);
        $this->assertEquals('John Doe', $processedArticles[0]['author']);
    }
}
