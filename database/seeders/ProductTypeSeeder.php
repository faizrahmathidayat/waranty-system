<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'KACA_FILM', 'name' => 'Kaca Film'],
            ['code' => 'COATING', 'name' => 'Coating'],
            ['code' => 'PPF', 'name' => 'Paint Protection Film'],
            ['code' => 'KACA_FILM_GEDUNG', 'name' => 'Kaca Film Gedung'],
        ] as $type) {
            $values = $type + ['description' => null, 'is_active' => true, 'updated_at' => now()];
            $existing = DB::table('product_types')->where('code', $type['code']);
            if ($existing->exists()) {
                $existing->update($values);
            } else {
                DB::table('product_types')->insert($values + ['created_at' => now()]);
            }
        }
    }
}
