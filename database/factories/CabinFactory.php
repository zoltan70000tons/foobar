<?php

namespace Database\Factories;

use App\Models\CabinCategory;
use App\Models\CabinSpec;
use App\Models\CabinType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cabin>
 */
class CabinFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cabin_type_id' => CabinType::inRandomOrder()->first()?->id ?? CabinType::factory(),
            'cabin_category_id' => CabinCategory::factory(),
            'cabin_spec_id' => CabinSpec::factory(),
            'inventory' => 1,
            'notes' => implode(' ', fake()->sentences(3)),
            //'tags' => null,
            'status' => 'AVAILABLE',
            'internal_notes' => implode(' ', fake()->words(3)),
        ];
    }
}
