<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_customer' => $this->faker->name(),
            'no_hp' => $this->faker->numerify('08##########'),
            'email' => $this->faker->unique()->safeEmail(),
            'alamat' => $this->faker->address(),
            'status' => 'enabled',
        ];
    }
}
