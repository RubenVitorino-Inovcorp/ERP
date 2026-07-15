<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use Illuminate\Database\Seeder;

class DocumentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Faturas',
            'Recibos',
            'Contratos',
            'Manuais',
            'Relatórios',
            'Multimédia',
            'Comprovativos de Pagamento',
            'Outros',
        ];

        foreach ($categories as $category) {
            DocumentCategory::firstOrCreate(['name' => $category]);
        }
    }
}
