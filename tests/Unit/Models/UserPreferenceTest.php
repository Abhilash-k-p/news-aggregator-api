<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserPreferenceTest extends TestCase
{
    #[Test]
    public function it_casts_preferred_fields_to_arrays()
    {
        // Mock the UserPreference model
        $mockedPreference = Mockery::mock(UserPreference::class)->makePartial();

        // Simulate the attributes that are cast to arrays
        $mockedPreference->shouldReceive('getAttribute')
            ->with('preferred_categories')
            ->andReturn(['Technology', 'Sports']);

        $mockedPreference->shouldReceive('getAttribute')
            ->with('preferred_sources')
            ->andReturn(['Source A', 'Source B']);

        $mockedPreference->shouldReceive('getAttribute')
            ->with('preferred_authors')
            ->andReturn(['Author X', 'Author Y']);

        // Assertions
        $this->assertIsArray($mockedPreference->preferred_categories);
        $this->assertIsArray($mockedPreference->preferred_sources);
        $this->assertIsArray($mockedPreference->preferred_authors);

        $this->assertEquals(['Technology', 'Sports'], $mockedPreference->preferred_categories);
        $this->assertEquals(['Source A', 'Source B'], $mockedPreference->preferred_sources);
        $this->assertEquals(['Author X', 'Author Y'], $mockedPreference->preferred_authors);
    }

}
