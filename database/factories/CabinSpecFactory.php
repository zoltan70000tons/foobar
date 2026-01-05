<?php

namespace Database\Factories;

use App\Models\CabinSpec;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CabinSpec>
 */
class CabinSpecFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'cabin_number' => fake()->numberBetween(9100, 9999),
            'deck' => fake()->numberBetween(1, 12),
            'total_berths' => fake()->numberBetween(2, 4),
            'lower_bed_type_1' => substr(fake()->word(), 0, 5),
            'lower_bed_type_2' => substr(fake()->word(), 0, 5),
            'upper_berths' => fake()->numberBetween(0, 2),
            'accessible' => fake()->boolean,
            'connects_with' => null,
            'location' => fake()->randomElement(['AF', 'MS', 'FW']),
            'balcony' => fake()->boolean,
            'obstructed_view' => fake()->boolean,
        ];
    }
}
