<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Building>
 */
class BuildingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_customer' => Customer::factory(),
            'nama_bangunan' => $this->faker->company().' Building',
            'alamat' => $this->faker->address(),
            'status' => 'active',
        ];
    }
}
