<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /** Membuat data fake/dummy untuk article */
        return [
            'title' => fake()->word(),
            'content' => fake()->sentence(100),
            'publish_date' => fake()->date()
        ];
    }
}
