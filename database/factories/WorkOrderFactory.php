<?php

namespace Database\Factories;

use App\Models\Entity;
use App\Models\Order;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrder>
 */
class WorkOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => fake()->unique()->numerify('OT-2026/####'),
            'entity_id' => Entity::inRandomOrder()->first()->id ?? Entity::factory(),
            'order_id' => Order::inRandomOrder()->first()->id ?? null,
            'calendar_event_id' => null,
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(['baixa', 'normal', 'alta', 'urgente']),
            'status' => fake()->randomElement(['pendente', 'em_curso', 'concluida', 'cancelada']),
            'expected_duration' => fake()->randomElement([60, 150, 240]),
            'actual_duration' => fake()->optional()->randomElement([75, 120, 270]),
        ];
    }
}
