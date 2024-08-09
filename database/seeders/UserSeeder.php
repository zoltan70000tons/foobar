<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Container\Container;
use Faker\Generator;
use App\Models\User;

class UserSeeder extends Seeder
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
    User::updateOrCreate(
      ['email' => 'admin@70000tons.com'],
      [
        'name' => 'admin',
        'lastname' => '',
        'middlename' => '',
        'password' => Hash::make('password'),
        'created_at' => $this->faker->dateTime($max = 'now'),
        'updated_at' => $this->faker->dateTime($max = 'now'),
        'city' => $this->faker->city,
        'state' => $this->faker->state,
        'zipcode' => $this->faker->postcode,
      ]
    );
    foreach (range(1, 20) as $index) {
      $name = $this->faker->firstname;
      User::updateOrCreate(
        ['email' => $name . '@70000tons.com'],
        [
          'name' => $name,
          'lastname' => $this->faker->lastname,
          'middlename' => $this->faker->firstname,
          'password' => Hash::make('password'),
          'created_at' => $this->faker->dateTime($max = 'now'),
          'updated_at' => $this->faker->dateTime($max = 'now'),
          'city' => $this->faker->city,
          'state' => $this->faker->state,
          'zipcode' => $this->faker->postcode,
        ]
      );
    }
  }
}
