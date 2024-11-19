<?php

namespace Tests\Unit\Services\Source\Vendors;

use App\Models\Source;
use App\Services\Source\Vendors\GuardianApiService;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GuardianApiServiceTest extends TestCase
{
    protected $guardianApiService;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock source object
        $this->mockSource = Mockery::mock(Source::class)->makePartial();
        $this->mockSource->base_url = 'https://content.guardianapis.com';
        $this->mockSource->api_key = 'test-api-key';
        $this->mockSource->id = 2;

        // Mock the BaseApiService dependencies
        $this->guardianApiService = Mockery::mock(GuardianApiService::class, [$this->mockSource])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    #[Test]
    public function it_fetches_articles_and_processes_them_correctly()
    {
        // Mock the getCategories method
        $this->guardianApiService->shouldReceive('getCategories')
            ->andReturn(collect([
                (object)['name' => 'sports', 'id' => 1],
            ]));

        // Mock the makeRequest method
        $this->guardianApiService->shouldReceive('makeRequest')
            ->andReturn([
                'response' => [
                    'results' => [
                        [
                            'id' => 'article_1',
                            'fields' => [
                                'headline' => 'Article 1',
                                'bodyText' => 'Content of article 1',
                                'byline' => 'Author 1',
                            ],
                            'webUrl' => 'https://example.com/article1',
                            'webPublicationDate' => '2024-11-19T00:00:00Z',
                        ]
                    ]
                ]
            ]);

        // Run the method
        $articles = $this->guardianApiService->fetchArticles();

        // Assertions
        $this->assertCount(1, $articles);
        $this->assertEquals('Article 1', $articles[0]['title']);
        $this->assertEquals('article_1', $articles[0]['source_article_id']);
        $this->assertEquals('Content of article 1', $articles[0]['content']);
        $this->assertEquals('https://example.com/article1', $articles[0]['url']);
        $this->assertEquals('2024-11-19 00:00:00', $articles[0]['published_at']);
        $this->assertEquals(1, $articles[0]['category_id']);
        $this->assertEquals('Author 1', $articles[0]['author']);
    }

    #[Test]
    public function it_logs_error_if_no_articles_returned()
    {
        // Mock the getCategories method
        $this->guardianApiService->shouldReceive('getCategories')
            ->andReturn(collect([
                (object)['name' => 'sports', 'id' => 1],
            ]));

        // Mock the makeRequest method to return no results
        $this->guardianApiService->shouldReceive('makeRequest')
            ->andReturn([]);

        // Mock Log::error and match the parameters
        Log::shouldReceive('error')
            ->once()
            ->with(
                'GuardianApi Error',
                Mockery::on(function ($params) {
                    // Check if the parameters contain what we expect
                    return isset($params['response']) && $params['response'] === '[]'
                        && isset($params['url']) && $params['url'] === 'https://content.guardianapis.com/sport';
                })
            )
            ->andReturnNull();

        // Run the method
        $articles = $this->guardianApiService->fetchArticles();

        // Assert no articles are returned
        $this->assertCount(0, $articles);
    }

    #[Test]
    public function it_processes_articles_correctly()
    {
        $articles = [
            [
                'id' => 'article_1',
                'fields' => [
                    'headline' => 'Article 1',
                    'bodyText' => 'Content of article 1',
                    'byline' => 'Author 1',
                ],
                'webUrl' => 'https://example.com/article1',
                'webPublicationDate' => '2024-11-19T00:00:00Z',
            ]
        ];

        $processedArticles = $this->guardianApiService->processArticles($articles, 1);

        // Assertions
        $this->assertCount(1, $processedArticles);
        $this->assertEquals('Article 1', $processedArticles[0]['title']);
        $this->assertEquals('article_1', $processedArticles[0]['source_article_id']);
        $this->assertEquals('Content of article 1', $processedArticles[0]['content']);
        $this->assertEquals('https://example.com/article1', $processedArticles[0]['url']);
        $this->assertEquals('2024-11-19 00:00:00', $processedArticles[0]['published_at']);
        $this->assertEquals(1, $processedArticles[0]['category_id']);
        $this->assertEquals(2, $processedArticles[0]['source_id']);
        $this->assertEquals('Author 1', $processedArticles[0]['author']);
    }
}
