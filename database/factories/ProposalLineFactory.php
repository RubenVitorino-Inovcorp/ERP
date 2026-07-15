<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Entity;
use App\Models\Proposal;
use App\Models\ProposalLine;
use App\Models\VatRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProposalLine>
 */
class ProposalLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'proposal_id' => Proposal::inRandomOrder()->first()->id ?? Proposal::factory(),
            'article_id' => Article::inRandomOrder()->first()->id ?? Article::factory(),
            'supplier_id' => Entity::whereIn('type', ['fornecedor', 'ambos'])->inRandomOrder()->first()->id ?? null,
            'cost_price' => fake()->randomFloat(2, 2, 500),
            'quantity' => fake()->numberBetween(1, 10),
            'unit_price' => fake()->randomFloat(2, 5, 1000),
            'vat_rate_id' => VatRate::inRandomOrder()->first()->id ?? 1,
        ];
    }
}
