<?php

namespace Database\Factories;

use App\Models\Entity;
use App\Models\Proposal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proposal>
 */
class ProposalFactory extends Factory
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
            'proposal_date' => fake()->dateTimeBetween('-2 months', 'now'),
            'entity_id' => Entity::whereIn('type', ['cliente', 'ambos'])->inRandomOrder()->first()->id ?? Entity::factory()->create(['type' => 'cliente'])->id,
            'validity_date' => fake()->dateTimeBetween('now', '+1 month'),
            'status' => fake()->randomElement(['rascunho', 'fechado']),
        ];
    }
}
