<?php

namespace Database\Seeders;

use App\Enums\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Container\Container;
use Faker\Generator;
use App\Models\User;
use App\Models\SurvivorNumber;
use Illuminate\Support\Str;
use App\Helpers\CustomerHelper;
use App\Models\UserDetail;
use App\Models\CustomerAddress;

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
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Seed a default system user
    $systemUser = User::firstOrCreate(
      ['email' => 'system@70000tons.com'],
      [
        'username' => 'system',
        'password' => Hash::make(Str::random(32)), // Generate a random, strong password
        'created_at' => now(),
        'updated_at' => now(),
        'organization_id' => env('ORGANIZATION_ID', 1),
      ]
    );

    //Seed users without 'Customer' role
    foreach (range(1, 20) as $index) {
      $name = $this->faker->firstname;
      $user = User::updateOrCreate(
        ['email' => Str::lower($name) . '@70000tons.com'],
        [
          'password' => Hash::make('password'),
          'created_at' => $this->faker->dateTime($max = 'now'),
          'updated_at' => $this->faker->dateTime($max = 'now'),
          'organization_id' => env('ORGANIZATION_ID', 1),
          'username' => $name . $index,
        ]
      );
      UserDetail::create([
        'user_id' => $user->id,
        'gender' => $this->faker->randomElement(['M', 'F']),
        'first_name' => strtoupper($this->faker->firstname),
        'middle_name' => strtoupper($this->faker->firstName),
        'last_name' => strtoupper($this->faker->lastName),
        'dob' => $this->faker->date(),
        'citizenship' => $this->faker->countryISOAlpha3(),
        'phone' => $this->faker->e164PhoneNumber(),
        'avatar' => $this->faker->imageUrl(),
        'emergency_c_name' => $this->faker->name,
        'emergency_c_phone' => $this->faker->e164PhoneNumber(),
        'language' => $this->faker->randomElement(['es', 'de', 'en']), // Only ESP, DEU, or ENG
      ]);

      CustomerAddress::updateOrCreate(
        ['user_id' => $user->id],
        [
          'address_first' => $this->faker->streetAddress,
          'address_second' => $this->faker->secondaryAddress,
          'city' => $this->faker->city,
          'state' => $this->faker->state,
          'postal_code' => $this->faker->postcode,
          'country' => $this->faker->countryISOAlpha3(),
        ]
      );
    }

    // Seed 1 users with the same email address but different names and survivor numbers
    $commonEmail1 = 'common1@customers.test';
    foreach (range(1, 1) as $index) {
      $name = 'cus' . $this->faker->firstname;
      $user = User::create([
        'email' => $commonEmail1,
        'password' => Hash::make('password'),
        'created_at' => $this->faker->dateTime($max = 'now'),
        'updated_at' => $this->faker->dateTime($max = 'now'),
        'organization_id' => env('ORGANIZATION_ID', 1),
      ]);

      UserDetail::create([
        'user_id' => $user->id,
        'gender' => $this->faker->randomElement(['M', 'F']),
        'first_name' => strtoupper($this->faker->firstname),
        'middle_name' => strtoupper($this->faker->firstName),
        'last_name' => strtoupper($this->faker->lastName),
        'dob' => $this->faker->date(),
        'citizenship' => $this->faker->countryISOAlpha3(),
        'phone' => $this->faker->e164PhoneNumber(),
        'avatar' => $this->faker->imageUrl(),
        'emergency_c_name' => $this->faker->name,
        'emergency_c_phone' => $this->faker->e164PhoneNumber(),
        'language' => $this->faker->randomElement(['es', 'de', 'en']), // Only ESP, DEU, or ENG
      ]);

      CustomerAddress::updateOrCreate(
        ['user_id' => $user->id],
        [
          'address_first' => $this->faker->streetAddress,
          'address_second' => $this->faker->secondaryAddress,
          'city' => $this->faker->city,
          'state' => $this->faker->state,
          'postal_code' => $this->faker->postcode,
          'country' => $this->faker->countryISOAlpha3(),
        ]
      );

      // Assign 'Customer' role
      setPermissionsTeamId(1);
      $user->assignRole('Customer');

      // Generate a unique survivor number
      $survivorNumber = CustomerHelper::generateSurvivorNumber();

      // Save survivor number in the survivor_numbers table
      SurvivorNumber::create([
        'user_id' => $user->id,
        'survivor_number' => $survivorNumber,
      ]);
    }

    foreach (range(1, 16) as $index) {
      $name = 'cus' . $this->faker->firstname;
      $user = User::create([
        'email' => 'customer_' . $index . '@customers.test',
        'password' => Hash::make('password'),
        'created_at' => $this->faker->dateTime($max = 'now'),
        'updated_at' => $this->faker->dateTime($max = 'now'),
        'organization_id' => env('ORGANIZATION_ID', 1),
        'email_verified_at' => null,
        'user_activated_at' => null,
      ]);

      // Assign 'Customer' role
      setPermissionsTeamId(1);
      $user->assignRole('Customer');

      // Generate a unique survivor number
      $survivorNumber = CustomerHelper::generateSurvivorNumber();

      // Save survivor number in the survivor_numbers table
      SurvivorNumber::create([
        'user_id' => $user->id,
        'survivor_number' => $survivorNumber,
      ]);

      UserDetail::create([
        'user_id' => $user->id,
        'gender' => $this->faker->randomElement(['M', 'F']),
        'first_name' => strtoupper($this->faker->firstName),
        'middle_name' => strtoupper($this->faker->firstName),
        'last_name' => strtoupper($this->faker->lastName),
        'dob' => $this->faker->date(),
        'citizenship' => $this->faker->countryISOAlpha3(),
        'phone' => $this->faker->e164PhoneNumber(),
        'avatar' => $this->faker->imageUrl(),
        'emergency_c_name' => $this->faker->name,
        'emergency_c_phone' => $this->faker->e164PhoneNumber(),
        'language' => $this->faker->randomElement(['es', 'de', 'en']), // Only ESP, DEU, or ENG
      ]);

      CustomerAddress::updateOrCreate(
        ['user_id' => $user->id],
        [
          'address_first' => $this->faker->streetAddress,
          'address_second' => $this->faker->secondaryAddress,
          'city' => $this->faker->city,
          'state' => $this->faker->state,
          'postal_code' => $this->faker->postcode,
          'country' => $this->faker->countryISOAlpha3(),
        ]
      );
    }


    // =====================================================================================
    
    //Commented out for now, once we move to production, we can populate the users from here

    // $path = database_path('seeders/data/users_seed_data.json');

    // if (!file_exists($path)) {
    //     throw new \Exception("Users file not found: $path");
    // }

    // $users = json_decode(file_get_contents($path), true);

    // foreach ($users as $user) {
    //     $this->create70000tonsUsers(
    //         $user['email'],
    //         \App\Enums\Roles::{$user['role']},
    //         $user['gender'],
    //         $user['first_name'],
    //         $user['last_name']
    //     );
    // }

    //======================================================================================
   

  }




  private function create70000tonsUsers($email, $role,$gender,$firstname,$lastName){

    $username = Str::before($email, '@');
    $user = User::create([
      'username' => $username,
      'email' => $email,
      'password' => env('DEFAULT_PASSWORD') ?? throw new \Exception('DEFAULT_PASSWORD not set in .env'),
      'created_at' => $this->faker->dateTime($max = 'now'),
      'updated_at' => $this->faker->dateTime($max = 'now'),
      'organization_id' => env('ORGANIZATION_ID', 1),
      'email_verified_at' => null,
      'user_activated_at' => null,
    ]);

    UserDetail::create([
        'user_id' => $user->id,
        'gender' => $gender,
        'first_name' => strtoupper($firstname),
        'last_name' => strtoupper($lastName),
        'citizenship' => $this->faker->countryISOAlpha3(),
        'language' => 'en', // Only ESP, DEU, or ENG
      ]);
    $user->assignRole($role->value);

  }
}
