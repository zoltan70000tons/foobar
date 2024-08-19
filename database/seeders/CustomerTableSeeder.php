<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    // Create 8 customers with unique data
    Customer::factory()->count(8)->create();

    // Create two users with the same email address smtp@bspmi.com
    Customer::factory()->count(2)->create([
      'email' => 'smtp@bspmi.com',
    ]);
  }
}
