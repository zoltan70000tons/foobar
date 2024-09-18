<?php

namespace Database\Factories;

use App\Models\CustomerAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerAddressFactory extends Factory
{
  protected $model = CustomerAddress::class;

  public function definition()
  {
    return [
      'address_first' => $this->faker->streetAddress,
      'address_second' => $this->faker->optional()->secondaryAddress,
      'city' => $this->faker->city,
      'state' => $this->faker->state,
      'postal_code' => $this->faker->postcode,
      'country' => $this->faker->countryISOAlpha3(),
    ];
  }
}
