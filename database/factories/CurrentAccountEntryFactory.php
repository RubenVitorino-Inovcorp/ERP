<?php

namespace Database\Factories;

use App\Models\CurrentAccountEntry;
use App\Models\Entity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CurrentAccountEntry>
 */
class CurrentAccountEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entity_id' => Entity::inRandomOrder()->first()->id ?? Entity::factory(),
            'date' => fake()->dateTimeBetween('-2 months', 'now'),
            'document_type' => fake()->randomElement(['Fatura', 'Recibo', 'Nota de Crédito']),
            'document_number' => fake()->bothify('DOC-2026/####'),
            'description' => fake()->sentence(),
            'movement_type' => fake()->randomElement(['credit', 'debit']),
            'amount' => fake()->randomFloat(2, 10, 5000),
            'attachment_path' => null,
        ];
    }
}
