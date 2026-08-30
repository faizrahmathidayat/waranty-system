<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Treatment>
 */
class TreatmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('TREAT_????')),
            'name' => $this->faker->words(2, true),
            'description' => null,
            'order_category' => 'AUTOMOTIVE',
            'is_active' => true,
        ];
    }
}
