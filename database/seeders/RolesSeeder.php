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
use Spatie\Permission\Models\Permission;
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

        foreach (Permissions::cases() as $permissionEnum) {
            Permission::firstOrCreate(
                ['name' => $permissionEnum->value, 'guard_name' => 'web'],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                    'system' => 1
                ]
            );
        }

        $superAdminRole = Role::where('name', 'SuperAdmin')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        $permissionNames = DB::table('permissions')->pluck('name')->toArray();
       // dd($permissionNames);
        
        // Assign *all permissions* to SuperAdmin using syncPermissions
        $superAdminRole->syncPermissions($permissionNames);

        $password = Str::random(12);

        $superAdminId = User::firstOrCreate(
            ['username' => 'SuperAdmin'],
            [
                'id' => $this->faker->uuid,
                'email' => 'superadmin@70000tons.com',
                'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', $password)),
                'created_at' => now(),
                'updated_at' => now(),
                'organization_id' => env('ORGANIZATION_ID', 1)
            ]
        );
        
        $adminId = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'id' => $this->faker->uuid,
                'email' => 'admin@70000tons.com',
                'password' => Hash::make(env('ADMIN_PASSWORD', $password)),
                'created_at' => now(),
                'updated_at' => now(),
                'organization_id' => env('ORGANIZATION_ID', 1)
            ]
        );

        DB::table('model_has_roles')->updateOrInsert([
            'role_id' => $superAdminRole->id,
            'model_type' => User::class,
            'model_id' => $superAdminId->id,
        ], [
            'team_id' => env('ORGANIZATION_ID', 1)
        ]);
        
        DB::table('model_has_roles')->updateOrInsert([
            'role_id' => $adminRole->id,
            'model_type' => User::class,
            'model_id' => $adminId->id,
        ], [
            'team_id' => env('ORGANIZATION_ID', 1)
        ]);

        //specific permissions for other roles

        $this->assignToRole(Roles::Manager, [
            Permissions::ViewDashboard,
            Permissions::ViewUsers,
            Permissions::CreateUsers,
            Permissions::EditUsers,
            Permissions::DeleteUsers,
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
            Permissions::ViewRoles,
            Permissions::CreateRoles, 
            Permissions::EditRoles, 
            Permissions::DeleteRoles,
            Permissions::ViewPermissions,
            Permissions::CreatePermissions, 
            Permissions::EditPermissions, 
            Permissions::DeletePermissions,
            Permissions::AssignPermissions,
            Permissions::RevokePermissions,
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
            Permissions::DeletePassengerDiscounts,
            Permissions::CreatePassengerOnboardCredit,
            Permissions::DeletePassengerOnboardCredit,
            Permissions::ViewTags,
            Permissions::EditTags,
            Permissions::DeleteTags,
            Permissions::CreateTags,
            Permissions::ViewLogs,
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
            Permissions::DeletePassengerDiscounts,
            Permissions::CreatePassengerOnboardCredit,
            Permissions::DeletePassengerOnboardCredit,
            Permissions::ViewTags
        ]);

        $this->assignToRole(Roles::Admin, [ //Superadmin
            Permissions::ViewDashboard,
            Permissions::ViewUsers,
            Permissions::CreateUsers,
            Permissions::EditUsers,
            Permissions::DeleteUsers,
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
            Permissions::EditFullCabins,
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
            Permissions::CreateRoles, 
            Permissions::EditRoles, 
            Permissions::DeleteRoles,
            Permissions::ViewPermissions,
            Permissions::CreatePermissions, 
            Permissions::EditPermissions, 
            Permissions::DeletePermissions,
            Permissions::AssignPermissions,
            Permissions::RevokePermissions,
            Permissions::ViewBookings,
            Permissions::CreateBookings,
            Permissions::EditBookings,
            Permissions::DeleteBookings,
            Permissions::InterceptBookings,
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
            Permissions::DeletePassengerDiscounts,
            Permissions::CreatePassengerOnboardCredit,
            Permissions::DeletePassengerOnboardCredit,
            Permissions::ViewTags,
            Permissions::EditTags,
            Permissions::DeleteTags,
            Permissions::CreateTags,
            Permissions::ViewLogs,
        ]);

        $this->assignToRole(Roles::Owner, [ // Superadmin
            Permissions::ViewDashboard,
            Permissions::ViewUsers,
            Permissions::CreateUsers,
            Permissions::EditUsers,
            Permissions::DeleteUsers,
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
            Permissions::CreateRoles, 
            Permissions::EditRoles, 
            Permissions::DeleteRoles,
            Permissions::ViewPermissions,
            Permissions::CreatePermissions, 
            Permissions::EditPermissions, 
            Permissions::DeletePermissions,
            Permissions::AssignPermissions,
            Permissions::RevokePermissions,
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
            Permissions::DeletePassengerDiscounts,
            Permissions::CreatePassengerOnboardCredit,
            Permissions::DeletePassengerOnboardCredit,
            Permissions::ViewTags,
            Permissions::EditTags,
            Permissions::DeleteTags,
            Permissions::CreateTags,
            Permissions::ViewLogs,
        ]);

        $this->assignToRole(Roles::Trainee, [  // Only View Access
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
            // Permissions::DeletePassengerDiscounts,
            // Permissions::CreatePassengerOnboardCredit,
            // Permissions::DeletePassengerOnboardCredit
            Permissions::ViewTags
        ]);
    }


    private function assignToRole($roleEnum, $permissions)
    {
        $role = Role::where('name', $roleEnum->value)->first();
    
        if (!$role) {
            return;
        }
    
        $permissionNames = array_map(fn($permission) => $permission->value, $permissions);
    
        // Now safely sync the permissions
        $role->syncPermissions($permissionNames);
    }
}
