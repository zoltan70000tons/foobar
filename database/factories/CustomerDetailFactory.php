<?php

namespace Database\Factories;

use App\Models\CustomerDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerDetailFactory extends Factory
{
  protected $model = CustomerDetail::class;

  public function definition()
  {
    return [
      'gender' => $this->faker->randomElement(['M', 'F']),
      'first_name' => $this->faker->firstName,
      'middle_name' => $this->faker->optional()->firstName,
      'last_name' => $this->faker->lastName,
      'dob' => $this->faker->date,
      'citizenship' => $this->faker->countryISOAlpha3(),
      'phone' => $this->faker->e164PhoneNumber(),
      'avatar' => $this->faker->imageUrl(200, 200, 'people', true),
      'emergency_c_name' => $this->faker->name,
      'emergency_c_phone' => $this->faker->phoneNumber,
      'language' => $this->faker->randomElement(['ESP', 'DEU', 'ENG']), // Only ESP, DEU, or ENG
    ];
  }
}
