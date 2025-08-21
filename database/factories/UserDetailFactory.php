<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserDetail>
 */
class UserDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gender' => $this->faker->randomElement(['M', 'F']),
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'dob' => fake()->date('Y-m-d', now()->subYears(22)->toDateString()),
            'citizenship' => fake()->randomElement(array_keys(\Symfony\Component\Intl\Countries::getNames())),
            'phone' => fake()->phoneNumber,
            'avatar' => fake()->text,
            'emergency_c_name' => fake()->name,
            'emergency_c_phone' => fake()->phoneNumber,
            'language' => $this->faker->randomElement(['en', 'de', 'es']),
        ];
    }
}
