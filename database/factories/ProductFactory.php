<?php

namespace Database\Factories;

use App\Models\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama_produk' => $this->faker->words(3, true),
            'brand' => $this->faker->company(),
            'jenis' => $this->faker->word(),
            'status' => 'enabled',
            'keterangan' => null,
            'id_product_type' => ProductType::factory(),
            'kode_produk' => strtoupper($this->faker->unique()->bothify('PRD-#####')),
            'harga_default' => $this->faker->randomFloat(2, 100000, 5000000),
            'is_warranty_eligible' => true,
        ];
    }
}
