<?php

namespace Database\Factories;

use App\Models\VatRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VatRate>
 */
class VatRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Taxa Normal (23%)', 'Taxa Intermédia (13%)', 'Taxa Reduzida (6%)', 'Isento (0%)']),
            'value' => fake()->randomElement([23.00, 13.00, 6.00, 0.00]),
        ];
    }
}
