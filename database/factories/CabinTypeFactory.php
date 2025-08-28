<?php

namespace Database\Factories;

use App\Models\CabinCategory;
use App\Models\CabinSpec;
use App\Models\CabinType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CabinType>
 */
class CabinTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cabin_type' => fake()->randomElement(['Private Cabin', 'Single Male', 'Single Female']),
            'cabin_type_description' => implode(' ', fake()->sentences(3)),
        ];
    }
}
