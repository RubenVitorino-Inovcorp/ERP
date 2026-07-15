<?php

namespace Database\Factories;

use App\Models\Entity;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['cliente', 'fornecedor']),
            'number' => fake()->unique()->numberBetween(1000, 99999),
            'order_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'entity_id' => Entity::inRandomOrder()->first()->id ?? Entity::factory(),
            'proposal_id' => null,
            'status' => fake()->randomElement(['rascunho', 'fechado']),
        ];
    }
}
