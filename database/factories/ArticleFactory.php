<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence, // Generates a random title
            'source_article_id' => $this->faker->uuid, // Generates a unique identifier
            'content' => $this->faker->paragraphs(3, true), // Generates random content
            'url' => $this->faker->url, // Generates a random URL
            'published_at' => $this->faker->dateTime, // Generates a random timestamp
            'source_id' => Source::factory(), // Assumes Source factory exists
            'category_id' => Category::factory(), // Assumes Category factory exists
            'author' => $this->faker->name, // Generates a random name for the author
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
