<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Login>
 */
class LoginFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'username' => $this->faker->unique()->userName(),
            'password' => Hash::make('password'),
            'role' => 'Super Admin',
            'status' => 'Enabled',
        ];
    }
}
