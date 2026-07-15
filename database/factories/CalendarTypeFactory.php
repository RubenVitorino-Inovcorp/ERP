<?php

namespace Database\Factories;

use App\Models\CalendarType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarType>
 */
class CalendarTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Reunião', 'Intervenção Técnica', 'Visita Comercial', 'Formação']),
        ];
    }
}
