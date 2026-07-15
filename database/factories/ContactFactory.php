<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\ContactFunction;
use App\Models\Entity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => fake()->unique()->numberBetween(1000, 99999),
            'entity_id' => Entity::inRandomOrder()->first()->id ?? Entity::factory(),
            'name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'contact_function_id' => ContactFunction::inRandomOrder()->first()->id ?? 1,
            'phone' => fake()->optional()->numerify('2########'),
            'mobile' => fake()->numerify('9########'),
            'email' => fake()->unique()->safeEmail(),
            'gdpr_consent' => fake()->boolean(80),
            'notes' => fake()->optional()->paragraph(),
            'status' => fake()->boolean(90),
        ];
    }
}
