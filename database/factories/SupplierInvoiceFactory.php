<?php

namespace Database\Factories;

use App\Models\Entity;
use App\Models\SupplierInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierInvoice>
 */
class SupplierInvoiceFactory extends Factory
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
            'invoice_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
            'entity_id' => Entity::whereIn('type', ['fornecedor', 'ambos'])->inRandomOrder()->first()->id ?? Entity::factory()->create(['type' => 'fornecedor'])->id,
            'order_id' => null,
            'total_value' => fake()->randomFloat(2, 50, 5000),
            'document_path' => null,
            'proof_of_payment_path' => null,
            'status' => fake()->randomElement(['pendente', 'paga']),
        ];
    }
}
