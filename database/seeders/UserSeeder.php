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
            User::updateOrCreate(
                ['email' => Str::lower($name) . '@70000tons.com'],
                [
                    'username' => $name,
                    'password' => Hash::make('password'),
                    'created_at' => $this->faker->dateTime($max = 'now'),
                    'updated_at' => $this->faker->dateTime($max = 'now'),
                    'organization_id' => env('ORGANIZATION_ID', 1)
                ]
            );
        }

        // Seed users with 'Customer' role and generate survivor number
        foreach (range(1, 20) as $index) {
            $name = 'cus' . $this->faker->firstname;
            $user = User::updateOrCreate(
                ['email' => Str::lower($name) . '@customers.test'],
                [
                    'username' => $name,
                    'password' => Hash::make('password'),
                    'created_at' => $this->faker->dateTime($max = 'now'),
                    'updated_at' => $this->faker->dateTime($max = 'now'),
                    'organization_id' => env('ORGANIZATION_ID', 1)
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
    }
}