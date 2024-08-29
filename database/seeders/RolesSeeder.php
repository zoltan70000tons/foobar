<?php

namespace Database\Seeders;

use App\Models\User;
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
        // Insertar o actualizar roles
        $roles = [
            'SuperAdmin',
            'Admin',
            'Owner',
            'Manager',
            'Editor',
            'Agent',
            'Customer'
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role, 'team_id' => 1],
                [
                    'team_id' => 1,
                    'name' => $role,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                    'system' => 1
                ]
            );
        }

        // Insertar o actualizar permisos
        $permissions = [
            'View Roles',
            'Create Role',
            'Edit Role',
            'Delete Role',
            'View Permissions',
            'Create Permission',
            'Edit Permission',
            'Delete Permission',
            'View Users',
            'Create User',
            'Edit User',
            'Delete User',
            'View Orders',
            'Create Order',
            'Edit Order',
            'Delete Order',
            'View Events',
            'Create Event',
            'Edit Event',
            'Delete Event',
            'View Customers',
            'Create Customer',
            'Edit Customer',
            'Delete Customer',
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission],
                [
                    'name' => $permission,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                    'system' => 1
                ]
            );
        }

        $superAdminRoleId = DB::table('roles')->where('name', 'SuperAdmin')->value('id');
        $adminRoleId = DB::table('roles')->where('name', 'Admin')->value('id');
        $permissionIds = DB::table('permissions')->pluck('id')->toArray();

        $rolePermissionData = [];
        foreach ($permissionIds as $permissionId) {
            $rolePermissionData[] = [
                'role_id' => $superAdminRoleId,
                'permission_id' => $permissionId
            ];
        }

        DB::table('role_has_permissions')->insert($rolePermissionData);
        $password = Str::random(12);
        
        $superAdminId = DB::table('users')->insertGetId([
            'id' => $this->faker->uuid,
            'username' => 'SuperAdmin',
            'email' => 'superadmin@70000tons.com',
            'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', $password)),
            'created_at' => now(),
            'updated_at' => now(),
            'organization_id' => env('ORGANIZATION_ID', 1)
        ]);

       $adminId = DB::table('users')->insertGetId([
            'id' => $this->faker->uuid,
            'email' => 'admin@70000tons.com',
            'username' => 'admin',
            'password' => Hash::make(env('ADMIN_PASSWORD', $password)),
            'created_at' => $this->faker->dateTime($max = 'now'),
            'updated_at' => $this->faker->dateTime($max = 'now'),
            'organization_id' => env('ORGANIZATION_ID', 1)
        ]);

        DB::table('model_has_roles')->insert([
            'role_id' => $superAdminRoleId,
            'model_type' => 'App\Models\User',
            'model_id' => $superAdminId,
            'team_id' => env('ORGANIZATION_ID', 1)
        ]);

        DB::table('model_has_roles')->insert([
            'role_id' => $adminRoleId,
            'model_type' => 'App\Models\User',
            'model_id' => $adminId,
            'team_id' => env('ORGANIZATION_ID', 1)
        ]);
    }
}
