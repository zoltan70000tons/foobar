<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Container\Container;
use Faker\Generator;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
  /**
   * The current Faker instance.
   *
   * @var \Faker\Generator
   */
  protected $faker;

  /**
   * Create a new seeder instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->faker = $this->withFaker();
  }

  /**
   * Get a new Faker instance.
   *
   * @return \Faker\Generator
   */
  protected function withFaker()
  {
    return Container::getInstance()->make(Generator::class);
  }
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    DB::table('events')->insert([
      'name' => '70000TONS OF METAL 2025',
      'description' =>
        '60 Bands, 4 Days, 1 Cruise Ship, and only 3000 Tickets. This is 70000TONS OF METAL®, The Original, The World’s Biggest Heavy Metal Cruise!',
      'image' => 'http://umc-dev-assets.s3.us-east-2.amazonaws.com/events/res7u5JtjbgCTz2zoiu3bd8StkckaZwLpeCvNPWW.jpg',
      'address' => 'Miami, Florida - Ocho Rios Jamaica',
      'start_date' => '2026-01-30',
      'end_date' => '2026-02-04',
      'status' => 'PRE-SALE',
      'created_at' => Carbon::now(),
      'organization_id' => env('ORGANIZATION_ID', 1),
    ]);
  }
}
