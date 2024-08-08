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
    $users = [
      [
        'email' => 'tobias@70000tons.com',
        'name' => 'TOBIAS',
        'lastname' => 'SCHMIDT',
        'middlename' => '',
        'password' => Hash::make('password'),
        'created_at' => $this->faker->dateTime($max = 'now'),
        'updated_at' => $this->faker->dateTime($max = 'now'),
        'city' => $this->faker->city,
        'state' => $this->faker->state,
        'zipcode' => $this->faker->postcode,
      ],
      [
        'email' => 'andrew@70000tons.com',
        'name' => 'ANDREW',
        'lastname' => 'SHEPHERD',
        'middlename' => 'J.',
        'password' => Hash::make('password'),
        'created_at' => $this->faker->dateTime($max = 'now'),
        'updated_at' => $this->faker->dateTime($max = 'now'),
        'city' => $this->faker->city,
        'state' => $this->faker->state,
        'zipcode' => $this->faker->postcode,
      ],
      [
        'email' => 'esmee@70000tons.com',
        'name' => 'ESMEE',
        'lastname' => 'HOEK',
        'middlename' => '',
        'password' => Hash::make('password'),
        'created_at' => $this->faker->dateTime($max = 'now'),
        'updated_at' => $this->faker->dateTime($max = 'now'),
        'city' => $this->faker->city,
        'state' => $this->faker->state,
        'zipcode' => $this->faker->postcode,
      ],
      [
        'email' => 'patricia@70000tons.com',
        'name' => 'PATRICIA',
        'lastname' => 'KLOEPPEL',
        'middlename' => '',
        'password' => Hash::make('password'),
        'created_at' => $this->faker->dateTime($max = 'now'),
        'updated_at' => $this->faker->dateTime($max = 'now'),
        'city' => $this->faker->city,
        'state' => $this->faker->state,
        'zipcode' => $this->faker->postcode,
      ],
      [
        'email' => 'carlos@70000tons.com',
        'name' => 'CARLOS',
        'lastname' => 'CASTANEDA',
        'middlename' => '',
        'password' => Hash::make('password'),
        'created_at' => $this->faker->dateTime($max = 'now'),
        'updated_at' => $this->faker->dateTime($max = 'now'),
        'city' => $this->faker->city,
        'state' => $this->faker->state,
        'zipcode' => $this->faker->postcode,
      ],
      [
        'email' => 'caitlin@70000tons.com',
        'name' => 'CAITLIN',
        'lastname' => 'DELAPLACE',
        'middlename' => '',
        'password' => Hash::make('password'),
        'created_at' => $this->faker->dateTime($max = 'now'),
        'updated_at' => $this->faker->dateTime($max = 'now'),
        'city' => $this->faker->city,
        'state' => $this->faker->state,
        'zipcode' => $this->faker->postcode,
      ],
      [
        'email' => 'kim@70000tons.com',
        'name' => 'KIMBERLEY',
        'lastname' => 'THIESSEN',
        'middlename' => '',
        'password' => Hash::make('password'),
        'created_at' => $this->faker->dateTime($max = 'now'),
        'updated_at' => $this->faker->dateTime($max = 'now'),
        'city' => $this->faker->city,
        'state' => $this->faker->state,
        'zipcode' => $this->faker->postcode,
      ],
      [
        'email' => 'natalia@70000tons.com',
        'name' => 'NATALIA',
        'lastname' => 'STETTNER',
        'middlename' => '',
        'password' => Hash::make('password'),
        'created_at' => $this->faker->dateTime($max = 'now'),
        'updated_at' => $this->faker->dateTime($max = 'now'),
        'city' => $this->faker->city,
        'state' => $this->faker->state,
        'zipcode' => $this->faker->postcode,
      ],
      [
        'email' => 'fernando@70000tons.com',
        'name' => 'FERNANDO',
        'lastname' => 'RODRIGUEZ ACOSTA',
        'middlename' => '',
        'password' => Hash::make('password'),
        'created_at' => $this->faker->dateTime($max = 'now'),
        'updated_at' => $this->faker->dateTime($max = 'now'),
        'city' => $this->faker->city,
        'state' => $this->faker->state,
        'zipcode' => $this->faker->postcode,
      ],
      [
        'email' => 'nicanor@70000tons.com',
        'name' => 'NICANOR',
        'lastname' => 'ERRÁZURIZ BRUNA',
        'middlename' => '',
        'password' => Hash::make('password'),
        'created_at' => $this->faker->dateTime($max = 'now'),
        'updated_at' => $this->faker->dateTime($max = 'now'),
        'city' => $this->faker->city,
        'state' => $this->faker->state,
        'zipcode' => $this->faker->postcode,
      ],
    ];
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
