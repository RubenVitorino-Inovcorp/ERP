<?php

namespace Database\Factories;

use App\Models\CalendarAction;
use App\Models\CalendarEvent;
use App\Models\CalendarType;
use App\Models\Entity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarEvent>
 */
class CalendarEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'time' => fake()->time('H:i'),
            'duration' => fake()->randomElement([30, 60, 90, 120]), // minutes
            'entity_id' => Entity::inRandomOrder()->first()->id ?? Entity::factory(),
            'calendar_type_id' => CalendarType::inRandomOrder()->first()->id ?? CalendarType::factory(),
            'calendar_action_id' => CalendarAction::inRandomOrder()->first()->id ?? CalendarAction::factory(),
            'description' => fake()->sentence(),
            'status' => fake()->randomElement(['Agendado', 'Realizado', 'Cancelado']),
        ];
    }
}
