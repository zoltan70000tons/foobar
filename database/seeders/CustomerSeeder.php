<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerDetail;
use App\Models\CustomerAddress;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    // Create 8 customers with unique data
    $customers = Customer::factory()->count(8)->create();

    // Create related customer details and addresses
    foreach ($customers as $customer) {
      // Create customer details for each customer
      CustomerDetail::factory()->create([
        'customer_id' => $customer->id,
      ]);

      // Create 1 addresses for each customer
      CustomerAddress::factory()->count(1)->create([
        'customer_id' => $customer->id,
      ]);
    }

    // Create two customers with the same email
    $duplicateEmailCustomers = Customer::factory()->count(2)->create([
      'email' => 'smtp@bspmi.com',
    ]);

    foreach ($duplicateEmailCustomers as $customer) {
      // Create customer details and addresses for customers with the same email
      CustomerDetail::factory()->create(['customer_id' => $customer->id]);
      CustomerAddress::factory()->count(rand(1, 2))->create(['customer_id' => $customer->id]);
    }
  }
}
