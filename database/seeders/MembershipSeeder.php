<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\MembershipType;
use App\Models\Membership;

class MembershipSeeder extends Seeder
{
  /**
   * Run the database seeders.
   */
  public function run(): void
  {

    $customers = Customer::all();

    // Two test purpose we will not assign membership to two customers
    $customersWithoutMembership = $customers->random(2);
    $membershipTypes = MembershipType::all();

    // Assign random membership types to each customer except those without memberships
    foreach ($customers as $customer) {
      if ($customersWithoutMembership->contains($customer)) {
        continue;
      }

      Membership::factory()
        ->forCustomer($customer->id)
        ->create([
          'membership_id' => $membershipTypes->random()->id,
        ]);
    }
  }
}
