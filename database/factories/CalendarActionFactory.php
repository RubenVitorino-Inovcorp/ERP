<?php

namespace Database\Factories;

use App\Models\CalendarAction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarAction>
 */
class CalendarActionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Análise', 'Implementação', 'Manutenção', 'Apresentação', 'Follow-up']),
        ];
    }
}
