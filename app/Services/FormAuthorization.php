<?php

namespace App\Services;

use App\Enums\RolesEnum;
use Illuminate\Support\Facades\Auth;

class FormAuthorization
{
    /**
     * Aborts with 403 unless the current user holds one of the roles mapped
     * to $type. Types absent from $rolesByType are left unchecked, falling
     * through to the caller's own "type not recognized" handling.
     *
     * @param  array<string, RolesEnum[]>  $rolesByType
     */
    public static function guard(string $type, array $rolesByType): void
    {
        $allowedRoles = $rolesByType[$type] ?? null;
        if (! $allowedRoles) {
            return;
        }

        $roleNames = array_map(fn (RolesEnum $role) => $role->value, $allowedRoles);

        abort_if(! Auth::user()?->hasAnyRole($roleNames), 403, 'You are not authorized to perform this action.');
    }
}
