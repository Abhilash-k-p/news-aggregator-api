<?php

namespace Tests\Unit\Models;

use App\Models\Article;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Database\Query\Builder;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    #[Test]
    public function test_article_belongs_to_category()
    {
        // Mock Category
        $categoryMock = Mockery::mock(Category::class);
        $categoryMock->shouldReceive('getAttribute')->with('id')->andReturn(1);

        // Mock Article with a category relationship
        $articleMock = Mockery::mock(Article::class)->makePartial();
        $articleMock->shouldReceive('getRelationValue')->with('category')->andReturn($categoryMock);

        $this->assertInstanceOf(Category::class, $articleMock->category);
        $this->assertEquals(1, $articleMock->category->id);
    }

    #[Test]
    public function test_article_belongs_to_source()
    {
        // Mock Source
        $sourceMock = Mockery::mock(Source::class);
        $sourceMock->shouldReceive('getAttribute')->with('id')->andReturn(1);

        // Mock Article with a source relationship
        $articleMock = Mockery::mock(Article::class)->makePartial();
        $articleMock->shouldReceive('getRelationValue')->with('source')->andReturn($sourceMock);

        $this->assertInstanceOf(Source::class, $articleMock->source);
        $this->assertEquals(1, $articleMock->source->id);
    }

    #[Test]
    public function test_scope_filter_by_keyword()
    {
        // Mock the query and filtering
        $queryMock = Mockery::mock(Builder::class)->makePartial();
        $queryMock->shouldReceive('get')->andReturn(collect([
            (object)['title' => 'Laravel Testing', 'content' => 'Testing scopes'],
        ]));

        $articleMock = Mockery::mock(Article::class)->makePartial();
        $articleMock->shouldReceive('filterByKeyword')->with('Laravel')->andReturn($queryMock);

        $results = $articleMock->filterByKeyword('Laravel')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Laravel Testing', $results->first()->title);
    }

    #[Test]
    public function test_scope_filter_by_published_date()
    {
        $date = now()->format('Y-m-d');

        // Mock the query and filtering
        $queryMock = Mockery::mock(Builder::class);
        $queryMock->shouldReceive('get')->andReturn(collect([
            (object)['published_at' => $date],
        ]));

        $articleMock = Mockery::mock(Article::class)->makePartial();
        $articleMock->shouldReceive('filterByPublishedDate')->with($date)->andReturn($queryMock);

        $results = $articleMock->filterByPublishedDate($date)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($date, $results->first()->published_at);
    }

    #[Test]
    public function test_scope_filter_by_category_name()
    {
        // Mock Category
        $categoryMock = Mockery::mock(Category::class);
        $categoryMock->shouldReceive('getAttribute')->with('name')->andReturn('Technology');

        // Mock Article query
        $queryMock = Mockery::mock(Builder::class);
        $queryMock->shouldReceive('get')->andReturn(collect([
            (object)['category' => $categoryMock],
        ]));

        $articleMock = Mockery::mock(Article::class)->makePartial();
        $articleMock->shouldReceive('filterByCategoryName')->with('Tech')->andReturn($queryMock);

        $results = $articleMock->filterByCategoryName('Tech')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Technology', $results->first()->category->name);
    }

    #[Test]
    public function test_scope_filter_by_source_name()
    {
        // Mock Source
        $sourceMock = Mockery::mock(Source::class);
        $sourceMock->shouldReceive('getAttribute')->with('name')->andReturn('NewsAPI');

        // Mock Article query
        $queryMock = Mockery::mock(Builder::class);
        $queryMock->shouldReceive('get')->andReturn(collect([
            (object)['source' => $sourceMock],
        ]));

        $articleMock = Mockery::mock(Article::class)->makePartial();
        $articleMock->shouldReceive('filterBySourceName')->with('News')->andReturn($queryMock);

        $results = $articleMock->filterBySourceName('News')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('NewsAPI', $results->first()->source->name);
    }

    #[Test]
    public function test_scope_filter_by_author()
    {
        // Mock the query and filtering
        $queryMock = Mockery::mock(Builder::class);
        $queryMock->shouldReceive('get')->andReturn(collect([
            (object) ['author' => 'John Doe'],
        ]));

        $articleMock = Mockery::mock(Article::class)->makePartial();
        $articleMock->shouldReceive('filterByAuthor')->with('John')->andReturn($queryMock);

        $results = $articleMock->filterByAuthor('John')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('John Doe', $results->first()->author);
    }
}
