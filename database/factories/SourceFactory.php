<?php

namespace Database\Factories;

use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Source>
 */
class SourceFactory extends Factory
{

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company, // Generates a random company name
            'api_key' => $this->faker->optional()->uuid, // Generates a UUID or NULL
            'base_url' => $this->faker->url, // Generates a random base URL
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
