<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_customer' => Customer::factory(),
            'no_polisi' => strtoupper($this->faker->bothify('B ####??')),
            'merk' => $this->faker->randomElement(['Toyota', 'Honda', 'Mitsubishi']),
            'model' => $this->faker->word(),
            'warna' => $this->faker->safeColorName(),
            'tahun' => $this->faker->numberBetween(2015, 2026),
            'status' => 'active',
        ];
    }
}
