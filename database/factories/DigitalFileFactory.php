<?php

namespace Database\Factories;

use App\Models\DigitalFile;
use App\Models\DocumentCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DigitalFile>
 */
class DigitalFileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true).'.pdf',
            'document_category_id' => DocumentCategory::inRandomOrder()->first()->id ?? 1,
            'file_path' => 'files/dummy_file.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1024, 1024000),
            'uploaded_by' => User::inRandomOrder()->first()->id ?? 1,
        ];
    }
}
