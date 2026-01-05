<?php

namespace App\Enums;

enum Roles: string {
    case SuperAdmin = 'SuperAdmin';
    case Admin = 'Admin';
    case Owner = 'Owner';
    case Manager = 'Manager';
    case Editor = 'Editor';
    case Agent = 'Agent';
    case Customer = 'Customer';
    case Trainee = 'Trainee';
}
