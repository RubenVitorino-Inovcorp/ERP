<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Entity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entity>
 */
class EntityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['cliente', 'fornecedor', 'ambos']),
            'number' => fake()->unique()->numberBetween(1000, 99999),
            'nif' => fake()->unique()->numerify('5########'),
            'name' => fake()->company(),
            'address' => fake()->streetAddress(),
            'zip_code' => fake()->numerify('####-###'),
            'city' => fake()->city(),
            'country_id' => Country::inRandomOrder()->first()->id ?? 1,
            'phone' => fake()->numerify('2########'),
            'mobile' => fake()->numerify('9########'),
            'website' => fake()->url(),
            'email' => fake()->unique()->companyEmail(),
            'gdpr_consent' => fake()->boolean(80),
            'notes' => fake()->optional()->paragraph(),
            'status' => fake()->boolean(90),
        ];
    }
}
