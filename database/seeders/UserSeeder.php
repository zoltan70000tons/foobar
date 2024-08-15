<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Container\Container;
use Faker\Generator;
use App\Models\User;
use Illuminate\Support\Str;

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
    $password = Str::random(12);
    foreach (range(1, 20) as $index) {
      $name = $this->faker->firstname;
      User::updateOrCreate(
        ['email' => Str::lower($name).'@70000tons.com'],
        [
          'username' => $name,
          'password' => Hash::make(env('DEFAULT_PASSWORD', $password)),
          'created_at' => $this->faker->dateTime($max = 'now'),
          'updated_at' => $this->faker->dateTime($max = 'now'),
          'organization_id' => env('ORGANIZATION_ID', 1)

        ]
      );
    }
  }
}
