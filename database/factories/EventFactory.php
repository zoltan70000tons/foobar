<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'description' => implode(' ', fake()->sentences(3)),
            'image' => fake()->url,
            'address' => fake()->address,
            'start_date' => fake()->date(),
            'end_date' => fake()->date(),
            'status' => 'PUBLIC',
            'organization_id' => Organization::factory(),
            'code' => fake()->word,
            'url' => fake()->url,
        ];
    }
}
