<?php

namespace App\Enums;

enum Permissions: string
{
    case ViewDashboard = 'View Dashboard';

    // Manage Users
    case ViewUsers = 'View Users';
    case CreateUsers = 'Create Users';
    case EditUsers = 'Edit Users';
    case DeleteUsers = 'Delete Users';

    // Manage Customers
    case ViewCustomers = 'View Customers';
    case CreateCustomers = 'Create Customers';
    case EditCustomers = 'Edit Customers';
    case DeleteCustomers = 'Delete Customers';

    // Manage Events
    case ViewEvents = 'View Events';
    case CreateEvents = 'Create Events';
    case EditEvents = 'Edit Events';
    case DeleteEvents = 'Delete Events';

    // Manage Cabins
    case ViewCabins = 'View Cabins';
    case CreateCabins = 'Create Cabins';
    case EditCabins = 'Edit Cabins';
    case DeleteCabins = 'Delete Cabins';

    // Manage Cabin Categories
    case ViewCabinCategories = 'View Cabin Categories';
    case CreateCabinCategories = 'Create Cabin Categories';
    case EditCabinCategories = 'Edit Cabin Categories';
    case DeleteCabinCategories = 'Delete Cabin Categories';

    // Manage Taxes
    case ViewTaxes = 'View Taxes';
    case CreateTaxes = 'Create Taxes';
    case EditTaxes = 'Edit Taxes';
    case DeleteTaxes = 'Delete Taxes';

    // Manage Roles
    case ViewRoles = 'View Roles';
    case CreateRoles = 'Create Roles';
    case EditRoles = 'Edit Roles';
    case DeleteRoles = 'Delete Roles';

    // Manage Permissions
    case ViewPermissions = 'View Permissions';
    case CreatePermissions = 'Create Permissions';
    case EditPermissions = 'Edit Permissions';
    case DeletePermissions = 'Delete Permissions';
    case AssignPermissions = 'Assign Permissions';
    case RevokePermissions = 'Revoke Permissions';
    
    // Manage bookings

    case ViewBookings = 'View Bookings';
    case CreateBookings = 'Create Bookings';
    case EditBookings = 'Edit Bookings';
    case DeleteBookings = 'Delete Bookings';


}
