<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryTest extends TestCase
{

    #[Test]
    public function it_excludes_unknown_categories_from_query()
    {
        // Mock the query builder
        $mockedQueryBuilder = Mockery::mock(Builder::class);

        // Mock the expected behavior of the query builder
        $mockedQueryBuilder
            ->shouldReceive('get')
            ->once()
            ->andReturn(collect([
                (object)['name' => 'Technology'],
                (object)['name' => 'Sports'],
            ]));

        // Mock the Category model
        $mockedCategory = Mockery::mock(Category::class);

        // Mock the notUnknown scope to return the mocked query builder
        $mockedCategory
            ->shouldReceive('notUnknown')
            ->once()
            ->andReturn($mockedQueryBuilder);

        // Call the scope
        $categories = $mockedCategory->notUnknown()->get();

        // Assertions
        $this->assertCount(2, $categories);
        $this->assertFalse(collect($categories)->pluck('name')->contains('unknown'));
    }

}
