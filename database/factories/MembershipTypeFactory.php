<?php

namespace Database\Factories;

use App\Models\Cabin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MembershipType>
 */
class MembershipTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word,
            'stamp_image' => fake()->text,
            'booking_number_requirement' => 0,
            'discount_value' => fake()->numberBetween(0,10),
        ];
    }
}
