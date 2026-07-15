<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\VatRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
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
        return [
            'reference' => fake()->unique()->bothify('ART-####??'),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 5, 1000),
            'vat_rate_id' => VatRate::inRandomOrder()->first()->id ?? 1,
            'photo_path' => null,
            'notes' => fake()->optional()->paragraph(),
            'status' => fake()->boolean(95),
        ];
    }
}
