<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Container\Container;
use Faker\Generator;


class RolesSeeder extends Seeder
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
        DB::table('roles')->insert([
            'team_id' => 1,
            'name' => 'SuperAdmin',
            'guard_name' => 'web',
            'created_at' => $this->faker->dateTime($max = 'now'),
            'updated_at' => $this->faker->dateTime($max = 'now'),
            'system' => 1
        ]);

        DB::table('roles')->insert([
            'team_id' => 1,
            'name' => 'Admin',
            'guard_name' => 'web',
            'created_at' => $this->faker->dateTime($max = 'now'),
            'updated_at' => $this->faker->dateTime($max = 'now'),
            'system' => 1
        ]);
        DB::table('roles')->insert([
            'team_id' => 1,
            'name' => 'Manager',
            'guard_name' => 'web',
            'created_at' => $this->faker->dateTime($max = 'now'),
            'updated_at' => $this->faker->dateTime($max = 'now'),
            'system' => 1
        ]);

        DB::table('roles')->insert([
            'team_id' => 1,
            'name' => 'Editor',
            'guard_name' => 'web',
            'created_at' => $this->faker->dateTime($max = 'now'),
            'updated_at' => $this->faker->dateTime($max = 'now'),
            'system' => 1
        ]);

        DB::table('roles')->insert([
            'team_id' => 1,
            'name' => 'Agent',
            'guard_name' => 'web',
            'created_at' => $this->faker->dateTime($max = 'now'),
            'updated_at' => $this->faker->dateTime($max = 'now'),
            'system' => 1
        ]);
        DB::table('roles')->insert([
            'team_id' => 1,
            'name' => 'Customer',
            'guard_name' => 'web',
            'created_at' => $this->faker->dateTime($max = 'now'),
            'updated_at' => $this->faker->dateTime($max = 'now'),
            'system' => 1
        ]);
        DB::table('permissions')->insert([

            'name' => 'View role',
            'guard_name' => 'web',
            'created_at' => $this->faker->dateTime($max = 'now'),
            'updated_at' => $this->faker->dateTime($max = 'now'),
            'system' => 1
        ]);

        DB::table('permissions')->insert([

            'name' => 'Create role',
            'guard_name' => 'web',
            'created_at' => $this->faker->dateTime($max = 'now'),
            'updated_at' => $this->faker->dateTime($max = 'now'),
            'system' => 1
        ]);

        DB::table('permissions')->insert([

            'name' => 'Edit Role',
            'guard_name' => 'web',
            'created_at' => $this->faker->dateTime($max = 'now'),
            'updated_at' => $this->faker->dateTime($max = 'now'),
            'system' => 1
        ]);

        DB::table('permissions')->insert([

            'name' => 'Delete Role',
            'guard_name' => 'web',
            'created_at' => $this->faker->dateTime($max = 'now'),
            'updated_at' => $this->faker->dateTime($max = 'now'),
            'system' => 1
        ]);


    }
}