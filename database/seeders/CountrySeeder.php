<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Country::create(['name' => 'Portugal', 'code' => 'PT']);
        Country::create(['name' => 'Espanha', 'code' => 'ES']);
        Country::create(['name' => 'França', 'code' => 'FR']);
        Country::create(['name' => 'Alemanha', 'code' => 'DE']);
        Country::create(['name' => 'Itália', 'code' => 'IT']);
        Country::create(['name' => 'Bélgica', 'code' => 'BE']);
        Country::create(['name' => 'Holanda', 'code' => 'NL']);
    }
}
