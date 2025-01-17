<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Container\Container;
use Faker\Generator;
use App\Models\User;
use App\Models\UserDetail;

class UserDetailSeeder extends Seeder
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
        // Ensure permissions cache is cleared
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Retrieve all users that need details
        $users = User::all();

        foreach ($users as $user) {
            // Mock data for the user_details table
            UserDetail::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'gender' => $this->faker->randomElement(['male', 'female', 'other']),
                    'first_name' => $this->faker->firstName,
                    'middle_name' => $this->faker->optional()->firstName,
                    'last_name' => $this->faker->lastName,
                    'phone' => $this->faker->e164PhoneNumber() ,
                    'avatar' => $this->faker->imageUrl(300, 300, 'people', true, 'Avatar'),
                    'emergency_c_name' => $this->faker->name,
                    'emergency_c_phone' => $this->faker->e164PhoneNumber() ,
                ]
            );
        }
    }
}
