<?php

namespace App\Enums;

enum RolesEnum: string
{
    case SYSTEMADMIN = 'System Admin';
    case SYSTEMDEVELOPER = 'System Developer';
    case ROOMALLOCATOR = 'Room Allocator';
    case FINANCE = 'Finance';
    case REGISTRAR = 'Registrar';
    case SUPERADMIN = 'Super Admin';
}
