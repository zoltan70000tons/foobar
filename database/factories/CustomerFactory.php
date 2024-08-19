<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = Customer::class;

  /**
   * Define the model's default state.
   *
   * @return array
   */
  public function definition()
  {
    return [
      'name' => $this->faker->unique()->name,
      'survival_number' => $this->faker->unique()->numerify('#########'),
      'password' => Hash::make('password'),
      'policy' => $this->faker->boolean,
      'email' => $this->faker->unique()->safeEmail,
      'email_verified_at' => now(),
      'remember_token' => Str::random(10),
    ];
  }
}
