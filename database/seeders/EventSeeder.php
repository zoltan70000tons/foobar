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
    $exists = DB::table('events')->where('id', 1)->exists();

    if (!$exists) {
      DB::table('events')->insert([
        'id' => 1,
        'name' => '70000TONS OF METAL 2026',
        'description' =>
        '60 Bands, 4 Days, 1 Cruise Ship, and only 3000 Tickets. This is 70000TONS OF METAL®, The Original, The World’s Biggest Heavy Metal Cruise!',
        'image' => env('AWS_ASSETS_CDN') . '/events/70000TONS_OF_METAL_2026.jpg',
        'address' => 'Miami, Florida - Labadee',
        'start_date' => '2026-01-29',
        'end_date' => '2026-02-02',
        'status' => 'PRE-SALE',
        'url' => 'https://70000tons.com',
        'booked_stamp' => env('AWS_ASSETS_CDN') . '/events/70000TONS_OF_METAL_2026_BOOKED.jpg',
        'created_at' => Carbon::now(),
        'organization_id' => env('ORGANIZATION_ID', 1),
        'code' => '70K2026',
      ]);
    }

    DB::table('events')->insert([
      'id' => 2,
      'name' => '70000TONS OF METAL 2025',
      'description' =>
      '61 Bands, 4 Days, 1 Cruise Ship, and only 3000 Tickets. This is 70000TONS OF METAL®, The Original, The World’s Biggest Heavy Metal Cruise!',
      'image' => env('AWS_ASSETS_CDN') . '/events/70000TONS_OF_METAL_2025.jpg',
      'address' => 'Miami, FL – Ocho Rios, Jamaica – Miami, FL',
      'start_date' => '2025-01-30',
      'end_date' => '2025-02-03',
      'status' => 'CLOSED',
      'url' => 'https://70000tons.com',
      'created_at' => Carbon::now(),
      'organization_id' => env('ORGANIZATION_ID', 1),
      'code' => '70K2025',
    ]);

    DB::table('events')->insert([
      'id' => 3,
      'name' => '70000TONS OF METAL 2024',
      'description' =>
      '62 Bands, 4 Days, 1 Cruise Ship, and only 3000 Tickets. This is 70000TONS OF METAL®, The Original, The World’s Biggest Heavy Metal Cruise!',
      'image' => env('AWS_ASSETS_CDN') . '/events/70000TONS_OF_METAL_2024.jpg',
      'address' => 'Miami, FL – Puerto Plata, Dominican Republic – Miami, FL',
      'start_date' => '2024-01-29',
      'end_date' => '2024-02-02',
      'status' => 'CLOSED',
      'url' => 'https://70000tons.com',
      'created_at' => Carbon::now(),
      'organization_id' => env('ORGANIZATION_ID', 1),
      'code' => '70K2024',
    ]);

    DB::table('events')->insert([
      'id' => 4,
      'name' => '70000TONS OF METAL 2023',
      'description' =>
      '60 Bands, 4 Days, 1 Cruise Ship, and only 3000 Tickets. This is 70000TONS OF METAL®, The Original, The World’s Biggest Heavy Metal Cruise!',
      'image' => env('AWS_ASSETS_CDN') . '/events/70000TONS_OF_METAL_2023.jpg',
      'address' => 'Miami, FL – Bimini, Bahamas – Miami, FL',
      'start_date' => '2023-01-30',
      'end_date' => '2023-02-03',
      'status' => 'CLOSED',
      'url' => 'https://70000tons.com',
      'created_at' => Carbon::now(),
      'organization_id' => env('ORGANIZATION_ID', 1),
      'code' => '70K2023',
    ]);

    DB::table('events')->insert([
      'id' => 5,
      'name' => '70000TONS OF METAL 2020',
      'description' =>
      '62 Bands, 4 Days, 1 Cruise Ship, and only 3000 Tickets. This is 70000TONS OF METAL®, The Original, The World’s Biggest Heavy Metal Cruise!',
      'image' => env('AWS_ASSETS_CDN') . '/events/70000TONS_OF_METAL_2020.jpg',
      'address' => 'Ft. Lauderdale, FL – Cozumel, Mexico – Ft. Lauderdale, FL',
      'start_date' => '2020-01-07',
      'end_date' => '2020-01-11',
      'status' => 'CLOSED',
      'url' => 'https://70000tons.com',
      'created_at' => Carbon::now(),
      'organization_id' => env('ORGANIZATION_ID', 1),
      'code' => '70K2020',
    ]);

    DB::table('events')->insert([
      'id' => 6,
      'name' => '70000TONS OF METAL 2019',
      'description' =>
      '60 Bands, 4 Days, 1 Cruise Ship, and only 3000 Tickets. This is 70000TONS OF METAL®, The Original, The World’s Biggest Heavy Metal Cruise!',
      'image' => env('AWS_ASSETS_CDN') . '/events/70000TONS_OF_METAL_2019.jpg',
      'address' => 'Ft. Lauderdale, FL – Labadee, Haiti – Ft. Lauderdale, FL',
      'start_date' => '2019-01-31',
      'end_date' => '2019-02-04',
      'status' => 'CLOSED',
      'url' => 'https://70000tons.com',
      'created_at' => Carbon::now(),
      'organization_id' => env('ORGANIZATION_ID', 1),
      'code' => '70K2019',
    ]);

    DB::table('events')->insert([
      'id' => 7,
      'name' => '70000TONS OF METAL 2018',
      'description' =>
      '60 Bands, 4 Days, 1 Cruise Ship, and only 3000 Tickets. This is 70000TONS OF METAL®, The Original, The World’s Biggest Heavy Metal Cruise!',
      'image' => env('AWS_ASSETS_CDN') . '/events/70000TONS_OF_METAL_2018.jpg',
      'address' => 'Ft. Lauderdale, FL – Grand Turk, Turks and Caicos – Ft. Lauderdale, FL',
      'start_date' => '2018-02-01',
      'end_date' => '2018-02-05',
      'status' => 'CLOSED',
      'url' => 'https://70000tons.com',
      'created_at' => Carbon::now(),
      'organization_id' => env('ORGANIZATION_ID', 1),
      'code' => '70K2018',
    ]);

    DB::table('events')->insert([
      'id' => 8,
      'name' => '70000TONS OF METAL 2017',
      'description' =>
      '60 Bands, 4 Days, 1 Cruise Ship, and only 3000 Tickets. This is 70000TONS OF METAL®, The Original, The World’s Biggest Heavy Metal Cruise!',
      'image' => env('AWS_ASSETS_CDN') . '/events/70000TONS_OF_METAL_2017.jpg',
      'address' => 'Ft. Lauderdale, FL – Labadee, Haiti – Ft. Lauderdale, FL',
      'start_date' => '2017-02-02',
      'end_date' => '2017-02-06',
      'status' => 'CLOSED',
      'url' => 'https://70000tons.com',
      'created_at' => Carbon::now(),
      'organization_id' => env('ORGANIZATION_ID', 1),
      'code' => '70K2017',
    ]);

    DB::table('events')->insert([
      'id' => 9,
      'name' => '70000TONS OF METAL 2016',
      'description' =>
      '60 Bands, 4 Days, 1 Cruise Ship, and only 3000 Tickets. This is 70000TONS OF METAL®, The Original, The World’s Biggest Heavy Metal Cruise!',
      'image' => env('AWS_ASSETS_CDN') . '/events/70000TONS_OF_METAL_2016.jpg',
      'address' => 'Ft. Lauderdale, FL – Falmouth, Jamaica – Ft. Lauderdale, FL',
      'start_date' => '2016-02-04',
      'end_date' => '2016-02-08',
      'status' => 'CLOSED',
      'url' => 'https://70000tons.com',
      'created_at' => Carbon::now(),
      'organization_id' => env('ORGANIZATION_ID', 1),
      'code' => '70K2016',
    ]);

    DB::table('events')->insert([
      'id' => 10,
      'name' => '70000TONS OF METAL 2015',
      'description' =>
      '60 Bands, 4 Days, 1 Cruise Ship, and only 3000 Tickets. This is 70000TONS OF METAL®, The Original, The World’s Biggest Heavy Metal Cruise!',
      'image' => env('AWS_ASSETS_CDN') . '/events/70000TONS_OF_METAL_2015.jpg',
      'address' => 'Ft. Lauderdale, FL – Ocho Rios, Jamaica – Ft. Lauderdale, FL',
      'start_date' => '2015-01-22',
      'end_date' => '2015-01-26',
      'status' => 'CLOSED',
      'url' => 'https://70000tons.com',
      'created_at' => Carbon::now(),
      'organization_id' => env('ORGANIZATION_ID', 1),
      'code' => '70K2015',
    ]);

    DB::table('events')->insert([
      'id' => 11,
      'name' => '70000TONS OF METAL 2014',
      'description' =>
      '60 Bands, 4 Days, 1 Cruise Ship, and only 3000 Tickets. This is 70000TONS OF METAL®, The Original, The World’s Biggest Heavy Metal Cruise!',
      'image' => env('AWS_ASSETS_CDN') . '/events/70000TONS_OF_METAL_2014.jpg',
      'address' => 'Miami, FL – Costa Maya, Mexico – Miami, FL',
      'start_date' => '2014-01-27',
      'end_date' => '2014-01-31',
      'status' => 'CLOSED',
      'url' => 'https://70000tons.com',
      'created_at' => Carbon::now(),
      'organization_id' => env('ORGANIZATION_ID', 1),
      'code' => '70K2014',
    ]);

    DB::table('events')->insert([
      'id' => 12,
      'name' => '70000TONS OF METAL 2013',
      'description' =>
      '60 Bands, 4 Days, 1 Cruise Ship, and only 3000 Tickets. This is 70000TONS OF METAL®, The Original, The World’s Biggest Heavy Metal Cruise!',
      'image' => env('AWS_ASSETS_CDN') . '/events/70000TONS_OF_METAL_2013.jpg',
      'address' => 'Miami, FL – Cockburn Town, Turks and Caicos Islands – Miami, FL',
      'start_date' => '2013-01-28',
      'end_date' => '2013-02-01',
      'status' => 'CLOSED',
      'url' => 'https://70000tons.com',
      'created_at' => Carbon::now(),
      'organization_id' => env('ORGANIZATION_ID', 1),
      'code' => '70K2013',
    ]);

    DB::table('events')->insert([
      'id' => 13,
      'name' => '70000TONS OF METAL 2012',
      'description' =>
      '60 Bands, 4 Days, 1 Cruise Ship, and only 3000 Tickets. This is 70000TONS OF METAL®, The Original, The World’s Biggest Heavy Metal Cruise!',
      'image' => env('AWS_ASSETS_CDN') . '/events/70000TONS_OF_METAL_2012.jpg',
      'address' => 'Miami, FL – George Town, Cayman Islands – Miami, FL',
      'start_date' => '2012-01-23',
      'end_date' => '2012-01-27',
      'status' => 'CLOSED',
      'url' => 'https://70000tons.com',
      'created_at' => Carbon::now(),
      'organization_id' => env('ORGANIZATION_ID', 1),
      'code' => '70K2012',
    ]);

    DB::table('events')->insert([
      'id' => 14,
      'name' => '70000TONS OF METAL 2011',
      'description' =>
      '60 Bands, 4 Days, 1 Cruise Ship, and only 3000 Tickets. This is 70000TONS OF METAL®, The Original, The World’s Biggest Heavy Metal Cruise!',
      'image' => env('AWS_ASSETS_CDN') . '/events/70000TONS_OF_METAL_2011.jpg',
      'address' => 'Miami, FL – Cozumel, Mexico – Miami, FL',
      'start_date' => '2011-01-24',
      'end_date' => '2011-01-28',
      'status' => 'CLOSED',
      'url' => 'https://70000tons.com',
      'created_at' => Carbon::now(),
      'organization_id' => env('ORGANIZATION_ID', 1),
      'code' => '70K2011',
    ]);

    DB::table('events')->insert([
      'id' => 15,
      'name' => 'BARGE TO HELL 2012',
      'description' =>
      '42 Bands, 4 Days, 1 Cruise Ship, and only 3000 Tickets. This is BARGE TO HELL®, The World’s Most Extreme Metal Cruise!',
      'image' => env('AWS_ASSETS_CDN') . '/events/BARGE_TO_HELL_2012.jpg',
      'address' => 'Miami, FL – Nassau, Bahamas – Miami, FL',
      'start_date' => '2012-12-03',
      'end_date' => '2012-12-07',
      'status' => 'CLOSED',
      'url' => 'https://70000tons.com',
      'created_at' => Carbon::now(),
      'organization_id' => env('ORGANIZATION_ID', 1),
      'code' => 'BTH2012',
    ]);
  }
}
