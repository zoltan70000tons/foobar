<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MembershipType;
use App\Models\Membership;
use App\Models\User;

class MembershipSeeder extends Seeder
{
  /**
   * Run the database seeders.
   */
  public function run(): void
  {
    // Get all users with role customer
    $customers = User::role('Customer')->get();

    // For test purposes, we will not assign membership to two customers
    $customersWithoutMembership = $customers->random(2);
    $membershipTypes = MembershipType::all();

    // Assign random membership types to each customer except those without memberships
    foreach ($customers as $customer) {
      if ($customersWithoutMembership->contains($customer)) {
        continue;
      }


      // customrs which name is starting with "cus"
      if (substr($customer->username, 0, 3) === 'cus') {
        Membership::create([
          'user_id' => $customer->id,
          'membership_id' => 3,
        ]);

        // Assign the customer role
        // $customer->assignRole('Customer');
      }

      Membership::create([
        'user_id' => $customer->id,
        'membership_id' => $membershipTypes->random()->id,
      ]);
    }
  }
}
