<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\Permissions;
use App\Enums\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Container\Container;
use Faker\Generator;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    protected $faker;

    public function __construct()
    {
        $this->faker = $this->withFaker();
    }

    protected function withFaker()
    {
        return Container::getInstance()->make(Generator::class);
    }

    public function run(): void
    {

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (Roles::cases() as $role) {
            $roleName = $role->value;
            Role::updateOrCreate(
                ['name' => $roleName, 'team_id' => 1],
                [
                    'team_id' => 1,
                    'name' => $roleName,
                    'guard_name' => 'web',
                    'system' => 1
                ]
            );
        }

        foreach (Permissions::cases() as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission->value],
                [
                    'name' => $permission->value,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                    'system' => 1
                ]
            );
        }

        $superAdminRoleId = Role::where('name', 'SuperAdmin')->value('id');
        $adminRoleId = Role::where('name', 'Admin')->value('id');
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

        //specific permissions for other roles

        $this->assignToRole(Roles::Manager, [
            Permissions::ViewDashboard,
            // Permissions::ViewUsers,
            // Permissions::CreateUsers,
            // Permissions::EditUsers,
            // Permissions::DeleteUsers,
            Permissions::ViewCustomers,
            Permissions::CreateCustomers,
            Permissions::EditCustomers,
            Permissions::DeleteCustomers,
            Permissions::ViewEvents,
            // Permissions::CreateEvents, 
            // Permissions::EditEvents, 
            // Permissions::DeleteEvents,
            Permissions::ViewCabins,
            Permissions::CreateCabins,
            Permissions::EditCabins,
            Permissions::DeleteCabins,
            Permissions::ViewCabinCategories,
            Permissions::CreateCabinCategories,
            Permissions::EditCabinCategories,
            Permissions::DeleteCabinCategories,
            Permissions::ViewTaxes,
            Permissions::CreateTaxes,
            Permissions::EditTaxes,
            Permissions::DeleteTaxes,
            // Permissions::ViewRoles,
            // Permissions::CreateRoles, 
            // Permissions::EditRoles, 
            // Permissions::DeleteRoles,
            // Permissions::ViewPermissions,
            // Permissions::CreatePermissions, 
            // Permissions::EditPermissions, 
            // Permissions::DeletePermissions,
            // Permissions::AssignPermissions,
            // Permissions::RevokePermissions,
            Permissions::ViewBookings,
            Permissions::CreateBookings,
            Permissions::EditBookings,
            Permissions::DeleteBookings,
            Permissions::EditCabinInventory,
            Permissions::EditPassengers,
            Permissions::ResetSeat,
            Permissions::ViewFees,
            Permissions::CreateFees,
            Permissions::EditFees,
            Permissions::DeleteFees,
            Permissions::ViewAdjustments,
            Permissions::CreateAdjustments,
            Permissions::EditAdjustments,
            Permissions::DeleteAdjustments,
            Permissions::ViewPayments,
            Permissions::CreatePayments,
            Permissions::EditPayments,
            Permissions::DeletePayments,
            Permissions::SendEmail,
            Permissions::CreatePassengerDiscounts,
            Permissions::DeletePassengerDiscounts
        ]);
        $this->assignToRole(Roles::Agent, [
            Permissions::ViewDashboard,
            // Permissions::ViewUsers,
            // Permissions::CreateUsers,
            // Permissions::EditUsers,
            // Permissions::DeleteUsers,
            Permissions::ViewCustomers,
            Permissions::CreateCustomers,
            Permissions::EditCustomers,
            Permissions::DeleteCustomers,
            Permissions::ViewEvents,
            // Permissions::CreateEvents, 
            // Permissions::EditEvents, 
            // Permissions::DeleteEvents,
            Permissions::ViewCabins,
            // Permissions::CreateCabins, 
            // Permissions::EditCabins, 
            // Permissions::DeleteCabins,
            Permissions::ViewCabinCategories,
            // Permissions::CreateCabinCategories, 
            // Permissions::EditCabinCategories, 
            // Permissions::DeleteCabinCategories,
            Permissions::ViewTaxes,
            Permissions::CreateTaxes,
            Permissions::EditTaxes,
            Permissions::DeleteTaxes,
            // Permissions::ViewRoles,
            // Permissions::CreateRoles, 
            // Permissions::EditRoles, 
            // Permissions::DeleteRoles,
            // Permissions::ViewPermissions,
            // Permissions::CreatePermissions, 
            // Permissions::EditPermissions, 
            // Permissions::DeletePermissions,
            // Permissions::AssignPermissions,
            // Permissions::RevokePermissions,
            Permissions::ViewBookings,
            Permissions::CreateBookings,
            Permissions::EditBookings,
            Permissions::DeleteBookings,
            //Permissions::EditCabinInventory,
            Permissions::EditPassengers,
            Permissions::ResetSeat,
            Permissions::ViewFees,
            Permissions::CreateFees,
            Permissions::EditFees,
            Permissions::DeleteFees,
            Permissions::ViewAdjustments,
            Permissions::CreateAdjustments,
            Permissions::EditAdjustments,
            Permissions::DeleteAdjustments,
            Permissions::ViewPayments,
            Permissions::CreatePayments,
            Permissions::EditPayments,
            Permissions::DeletePayments,
            Permissions::SendEmail,
            Permissions::CreatePassengerDiscounts,
            Permissions::DeletePassengerDiscounts
        ]);

        $this->assignToRole(Roles::Admin, [
            Permissions::ViewDashboard,
            Permissions::ViewUsers,
            // Permissions::CreateUsers,
            // Permissions::EditUsers,
            // Permissions::DeleteUsers,
            Permissions::ViewCustomers,
            Permissions::CreateCustomers,
            Permissions::EditCustomers,
            Permissions::DeleteCustomers,
            Permissions::ViewEvents,
            Permissions::CreateEvents,
            Permissions::EditEvents,
            Permissions::DeleteEvents,
            Permissions::ViewCabins,
            Permissions::CreateCabins,
            Permissions::EditCabins,
            Permissions::DeleteCabins,
            Permissions::ViewCabinCategories,
            Permissions::CreateCabinCategories,
            Permissions::EditCabinCategories,
            Permissions::DeleteCabinCategories,
            Permissions::ViewTaxes,
            Permissions::CreateTaxes,
            Permissions::EditTaxes,
            Permissions::DeleteTaxes,
            Permissions::ViewRoles,
            // Permissions::CreateRoles, 
            // Permissions::EditRoles, 
            // Permissions::DeleteRoles,
            Permissions::ViewPermissions,
            // Permissions::CreatePermissions, 
            // Permissions::EditPermissions, 
            // Permissions::DeletePermissions,
            // Permissions::AssignPermissions,
            // Permissions::RevokePermissions,
            Permissions::ViewBookings,
            Permissions::CreateBookings,
            Permissions::EditBookings,
            Permissions::DeleteBookings,
            Permissions::EditCabinInventory,
            Permissions::EditPassengers,
            Permissions::ResetSeat,
            Permissions::ViewFees,
            Permissions::CreateFees,
            Permissions::EditFees,
            Permissions::DeleteFees,
            Permissions::ViewAdjustments,
            Permissions::CreateAdjustments,
            Permissions::EditAdjustments,
            Permissions::DeleteAdjustments,
            Permissions::ViewPayments,
            Permissions::CreatePayments,
            Permissions::EditPayments,
            Permissions::DeletePayments,
            Permissions::SendEmail,
            Permissions::CreatePassengerDiscounts,
            Permissions::DeletePassengerDiscounts
        ]);

        $this->assignToRole(Roles::Trainee, [
            Permissions::ViewDashboard,
            Permissions::ViewUsers,
            // Permissions::CreateUsers,
            // Permissions::EditUsers,
            // Permissions::DeleteUsers,
            Permissions::ViewCustomers,
            // Permissions::CreateCustomers,
            // Permissions::EditCustomers,
            // Permissions::DeleteCustomers,
            Permissions::ViewEvents,
            // Permissions::CreateEvents,
            // Permissions::EditEvents,
            // Permissions::DeleteEvents,
            Permissions::ViewCabins,
            // Permissions::CreateCabins,
            // Permissions::EditCabins,
            // Permissions::DeleteCabins,
            Permissions::ViewCabinCategories,
            // Permissions::CreateCabinCategories,
            // Permissions::EditCabinCategories,
            // Permissions::DeleteCabinCategories,
            Permissions::ViewTaxes,
            // Permissions::CreateTaxes,
            // Permissions::EditTaxes,
            // Permissions::DeleteTaxes,
            Permissions::ViewRoles,
            // Permissions::CreateRoles, 
            // Permissions::EditRoles, 
            // Permissions::DeleteRoles,
            Permissions::ViewPermissions,
            // Permissions::CreatePermissions, 
            // Permissions::EditPermissions, 
            // Permissions::DeletePermissions,
            // Permissions::AssignPermissions,
            // Permissions::RevokePermissions,
            Permissions::ViewBookings,
            // Permissions::CreateBookings,
            // Permissions::EditBookings,
            // Permissions::DeleteBookings,
            // Permissions::EditCabinInventory,
            // Permissions::EditPassengers,
            // Permissions::ResetSeat,
            // Permissions::ViewFees,
            // Permissions::CreateFees,
            // Permissions::EditFees,
            // Permissions::DeleteFees,
            Permissions::ViewAdjustments,
            // Permissions::CreateAdjustments,
            // Permissions::EditAdjustments,
            // Permissions::DeleteAdjustments,
            Permissions::ViewPayments,
            // Permissions::CreatePayments,
            // Permissions::EditPayments,
            // Permissions::DeletePayments,
            // Permissions::SendEmail,
            // Permissions::CreatePassengerDiscounts,
            // Permissions::DeletePassengerDiscounts
        ]);
    }


    private function assignToRole($role, $permissions)
    {
        $roleId = Role::where('name', $role->value)->value('id');

        $permissionNames = array_map(fn($permission) => $permission->value, $permissions);

        $permissionIds = DB::table('permissions')
            ->whereIn('name', $permissionNames)
            ->pluck('id')
            ->toArray();

        $rolePermissions = [];
        foreach ($permissionIds as $permissionId) {
            $rolePermissions[] = [
                'role_id' => $roleId,
                'permission_id' => $permissionId
            ];
        }

        DB::table('role_has_permissions')->insert($rolePermissions);
    }
}
