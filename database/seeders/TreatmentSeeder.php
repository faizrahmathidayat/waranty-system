<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TreatmentSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'KACA_FILM', 'name' => 'Kaca Film'],
            ['code' => 'COATING', 'name' => 'Coating'],
            ['code' => 'PPF', 'name' => 'Paint Protection Film'],
            ['code' => 'ANTI_KARAT', 'name' => 'Anti Karat'],
            ['code' => 'DETAILING', 'name' => 'Detailing'],
            ['code' => 'KACA_FILM_GEDUNG', 'name' => 'Kaca Film Gedung'],
        ] as $treatment) {
            $values = $treatment + ['description' => null, 'is_active' => true, 'updated_at' => now()];
            $existing = DB::table('treatments')->where('code', $treatment['code']);
            if ($existing->exists()) {
                $existing->update($values);
            } else {
                DB::table('treatments')->insert($values + ['created_at' => now()]);
            }
        }
    }
}
