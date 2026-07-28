<?php

namespace App\Services;

use App\Enums\RolesEnum;
use App\Http\Controllers\Admin\AccommodationController;
use App\Http\Controllers\Admin\AssignedRoomEpisodeController;
use App\Http\Controllers\Admin\DownloadController;
use App\Http\Controllers\Admin\DropdownController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventFeesController;
use App\Http\Controllers\Admin\EventVenueController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Auth;

class FormDeleteService
{
    /**
     * Roles allowed to delete each resource type, mirroring the role
     * middleware already applied to that resource's create/update routes.
     */
    private const ROLES_ALLOWED_TO_DELETE = [
        'user' => [RolesEnum::SYSTEMADMIN],
        'permission' => [RolesEnum::SYSTEMDEVELOPER],
        'role' => [RolesEnum::SYSTEMDEVELOPER],
        'dropdown_category' => [RolesEnum::SYSTEMADMIN, RolesEnum::SUPERADMIN],
        'dropdown' => [RolesEnum::SYSTEMADMIN, RolesEnum::SUPERADMIN],
        'venue' => [RolesEnum::SYSTEMADMIN, RolesEnum::SUPERADMIN],
        'accommodation' => [RolesEnum::SYSTEMADMIN, RolesEnum::ROOMALLOCATOR, RolesEnum::SUPERADMIN],
        'event' => [RolesEnum::SYSTEMADMIN, RolesEnum::SUPERADMIN],
        'fees' => [RolesEnum::SYSTEMADMIN, RolesEnum::SUPERADMIN],
        'roommate' => [RolesEnum::SYSTEMADMIN, RolesEnum::ROOMALLOCATOR, RolesEnum::SUPERADMIN],
        'financial_entry' => [RolesEnum::SYSTEMADMIN, RolesEnum::FINANCE, RolesEnum::SUPERADMIN],
        'downloads' => [RolesEnum::SYSTEMADMIN, RolesEnum::SUPERADMIN],
    ];

    public static function delete($type, $id)
    {
        $allowedRoles = self::ROLES_ALLOWED_TO_DELETE[$type] ?? null;

        if ($allowedRoles) {
            $roleNames = array_map(fn (RolesEnum $role) => $role->value, $allowedRoles);
            abort_if(! Auth::user()?->hasAnyRole($roleNames), 403, 'You are not authorized to perform this action.');
        }

        switch ($type) {
            case 'user':
                return RegisteredUserController::destroy($id);

            case 'permission':
                return PermissionController::destroy($id);

            case 'role':
                return RoleController::destroy($id);

            case 'dropdown_category':
                return DropdownController::destroyCategory($id);

            case 'dropdown':
                return DropdownController::destroy($id);

            case 'venue':
                return EventVenueController::destroy($id);

            case 'accommodation':
                return AccommodationController::destroy($id);

            case 'event':
                return EventController::destroy($id);

            case 'fees':
                return EventFeesController::destroy($id);

            case 'roommate':
                return AssignedRoomEpisodeController::destroy($id);

            case 'financial_entry':
                return FinanceController::financialEntryDelete($id);

            case 'downloads':
                return DownloadController::destroy($id);

            default:
                return 'No Form Selected';
        }
    }
}
