<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Technician>
 */
class TechnicianFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('TC-###')),
            'name' => $this->faker->name(),
            'phone' => $this->faker->numerify('08##########'),
            'is_active' => true,
        ];
    }
}
