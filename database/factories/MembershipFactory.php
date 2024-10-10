<?php

namespace Database\Factories;

use App\Models\Membership;
use App\Models\User;
use App\Models\MembershipType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membership>
 */
class MembershipFactory extends Factory
{
  protected $model = Membership::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'customer_id' => User::factory(),
      'membership_id' => MembershipType::all()->random()->id,
    ];
  }


  public function forCustomer($customerId)
  {
    return $this->state(function (array $attributes) use ($customerId) {
      return [
        'customer_id' => $customerId,
      ];
    });
  }
}
