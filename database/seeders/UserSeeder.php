<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Container\Container;
use Faker\Generator;
use App\Models\User;
use App\Models\SurvivorNumber;
use Illuminate\Support\Str;
use App\Helpers\CustomerHelper;
use App\Models\UserDetail;

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

        // Seed users without 'Customer' role
        foreach (range(1, 20) as $index) {
            $name = $this->faker->firstname;
            $user = User::updateOrCreate(
                ['email' => Str::lower($name) . '@70000tons.com'],
                [
                    'username' => $name,
                    'password' => Hash::make('password'),
                    'created_at' => $this->faker->dateTime($max = 'now'),
                    'updated_at' => $this->faker->dateTime($max = 'now'),
                    'organization_id' => env('ORGANIZATION_ID', 1)
                ]
            );
            UserDetail::create([
                'user_id' => $user->id, 
                'gender' => $this->faker->randomElement(['M', 'F']),
                'first_name' => $this->faker->firstname,
                'middle_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'dob' => $this->faker->date(),
                'citizenship' => $this->faker->country,
                'phone' => $this->faker->phoneNumber,
                'avatar' => $this->faker->imageUrl(),
                'emergency_c_name' => $this->faker->name,
                'emergency_c_phone' => $this->faker->phoneNumber,
            ]);
        }

        // Seed 2 users with the same email address but different names and survivor numbers
        $commonEmail1 = 'common1@customers.test';
        foreach (range(1, 2) as $index) {
            $name = 'cus' . $this->faker->firstname;
            $user = User::create([
                'email' => $commonEmail1,
                'username' => $name,
                'password' => Hash::make('password'),
                'created_at' => $this->faker->dateTime($max = 'now'),
                'updated_at' => $this->faker->dateTime($max = 'now'),
                'organization_id' => env('ORGANIZATION_ID', 1)
            ]);

            UserDetail::create([
                'user_id' => $user->id, 
                'gender' => $this->faker->randomElement(['M', 'F']),
                'first_name' => $this->faker->firstname,
                'middle_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'dob' => $this->faker->date(),
                'citizenship' => $this->faker->country,
                'phone' => $this->faker->phoneNumber,
                'avatar' => $this->faker->imageUrl(),
                'emergency_c_name' => $this->faker->name,
                'emergency_c_phone' => $this->faker->phoneNumber,
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
        }

        // Seed another user with a different email address
        $commonEmail2 = 'common2@customers.test';
        $name = 'cus' . $this->faker->firstname;
        $user = User::create([
            'email' => $commonEmail2,
            'username' => $name,
            'password' => Hash::make('password'),
            'created_at' => $this->faker->dateTime($max = 'now'),
            'updated_at' => $this->faker->dateTime($max = 'now'),
            'organization_id' => env('ORGANIZATION_ID', 1)
        ]);

        UserDetail::create([
            'user_id' => $user->id, 
            'gender' => $this->faker->randomElement(['M', 'F']),
            'first_name' => $this->faker->firstname,
            'middle_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'dob' => $this->faker->date(),
            'citizenship' => $this->faker->country,
            'phone' => $this->faker->phoneNumber,
            'avatar' => $this->faker->imageUrl(),
            'emergency_c_name' => $this->faker->name,
            'emergency_c_phone' => $this->faker->phoneNumber,
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

        foreach (range(1, 16) as $index) {
            $name = 'cus' . $this->faker->firstname;
            $user = User::create([
                'email' => Str::lower($name) . '@customers.test',
                'username' => $name,
                'password' => Hash::make('password'),
                'created_at' => $this->faker->dateTime($max = 'now'),
                'updated_at' => $this->faker->dateTime($max = 'now'),
                'organization_id' => env('ORGANIZATION_ID', 1),
                'email_verified_at' => now(),
                'user_activated_at' => now(),
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
                'first_name' => $this->faker->firstName,
                'middle_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'dob' => $this->faker->date(),
                'citizenship' => $this->faker->country,
                'phone' => $this->faker->phoneNumber,
                'avatar' => $this->faker->imageUrl(),
                'emergency_c_name' => $this->faker->name,
                'emergency_c_phone' => $this->faker->phoneNumber,
            ]);
        }
    }
}