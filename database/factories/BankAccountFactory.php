<?php

namespace Database\Factories;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BankAccount>
 */
class BankAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Conta Principal', 'Conta Secundária', 'Conta Poupança']),
            'bank_name' => fake()->randomElement(['CGD', 'Millennium BCP', 'Novo Banco', 'BPI', 'Santander']),
            'iban' => fake()->iban('PT'),
            'swift' => fake()->swiftBicNumber(),
            'status' => fake()->boolean(90),
        ];
    }
}
