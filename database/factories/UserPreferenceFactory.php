<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Source;
use App\Models\UserPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserPreferenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserPreference::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // Assumes a User factory exists
            'preferred_categories' => json_encode(Category::factory(3)),
            'preferred_sources' => json_encode(Source::factory(3)),
            'preferred_authors' => json_encode($this->faker->randomElements(
                ['Author A', 'Author B', 'Author C', 'Author X', 'Author Y'],
                rand(1, 3)
            )),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
