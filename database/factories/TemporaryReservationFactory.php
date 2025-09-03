<?php

namespace Database\Factories;

use App\Models\Cabin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TemporaryReservation>
 */
class TemporaryReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cabin_id' => Cabin::factory(),
            'user_id' => User::factory(),
            'cabin_number' => fake()->numberBetween(2,9999),
            'inventory' => fake()->numberBetween(1,9),
            'expires_at' => fake()->dateTimeBetween('+1 month', '+2 years'),
        ];
    }
}
