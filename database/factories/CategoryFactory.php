<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
        ];
    }
}
