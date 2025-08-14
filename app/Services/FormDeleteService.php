<?php

namespace App\Services;

use App\Http\Controllers\Admin\AccommodationController;
use App\Http\Controllers\Admin\AssignedRoomEpisodeController;
use App\Http\Controllers\Admin\DropdownController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventFeesController;
use App\Http\Controllers\Admin\EventVenueController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Auth\RegisteredUserController;

class FormDeleteService
{
    static public function delete($type, $id)
    {
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

            default:
                return "No Form Selected";
        }
    }
}
