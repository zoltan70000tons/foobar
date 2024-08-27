<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\MembershipType;

class MembershipSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // Fetch all customers
        $customers = Customer::all();

        // Fetch all membership types
        $membershipTypes = MembershipType::all();

        // Assign random membership types to each customer
        foreach ($customers as $customer) {
            DB::table('memberships')->insert([
                'customer_id' => $customer->id,
                'membership_id' => $membershipTypes->random()->id,
            ]);
        }
    }
}
