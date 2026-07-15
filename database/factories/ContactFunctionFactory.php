<?php

namespace Database\Factories;

use App\Models\ContactFunction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactFunction>
 */
class ContactFunctionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Diretor Geral', 'Gerente', 'Administrativo', 'Responsável de Compras', 'Técnico de IT']),
        ];
    }
}
