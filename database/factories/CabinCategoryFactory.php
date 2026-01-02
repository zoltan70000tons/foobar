<?php

namespace Database\Factories;

use App\Models\CabinCategorySpec;
use App\Models\CabinSpec;
use App\Models\Cruise;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CabinCategory>
 */
class CabinCategoryFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'price' => fake()->randomFloat(4, 1000, 6000),
            'cabin_category_spec_id' => CabinCategorySpec::factory(),
            'event_id' => Event::factory(),
        ];
    }
}
