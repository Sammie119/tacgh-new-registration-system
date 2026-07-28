<?php

namespace App\Services;

use App\Enums\RolesEnum;
use App\Models\Admin\Accommodation;
use App\Models\Admin\AccommodationBlock;
use App\Models\Admin\AccommodationRoom;
use App\Models\Admin\AssignPermissionToRole;
use App\Models\Admin\Dropdown;
use App\Models\Admin\EventFees;
use App\Models\Admin\OnlinePayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FormViewService
{
    /**
     * Roles allowed to view each resource type, mirroring the role
     * middleware already applied to that resource's admin pages/routes.
     */
    private const ROLES_ALLOWED_TO_VIEW = [
        'user_roles' => [RolesEnum::SYSTEMADMIN],
        'assign_permissions' => [RolesEnum::SYSTEMDEVELOPER],
        'dropdown' => [RolesEnum::SYSTEMADMIN, RolesEnum::SUPERADMIN],
        'accommodations' => [RolesEnum::SYSTEMADMIN, RolesEnum::ROOMALLOCATOR, RolesEnum::SUPERADMIN],
        'blocks_setup' => [RolesEnum::SYSTEMADMIN, RolesEnum::ROOMALLOCATOR, RolesEnum::SUPERADMIN],
        'generate_rooms' => [RolesEnum::SYSTEMADMIN, RolesEnum::ROOMALLOCATOR, RolesEnum::SUPERADMIN],
        'fees' => [RolesEnum::SYSTEMADMIN, RolesEnum::SUPERADMIN],
        'financial_clearance' => [RolesEnum::SYSTEMADMIN, RolesEnum::FINANCE, RolesEnum::SUPERADMIN],
    ];

    public static function view($type, $id)
    {
        FormAuthorization::guard($type, self::ROLES_ALLOWED_TO_VIEW);

        switch ($type) {
            case 'user_roles':
                $user = User::find($id);
                abort_if(! $user, 404, 'User not found.');

                $data['user'] = $user->id;
                $data['roles'] = Role::select('id', 'name')->get();
                $data['assigned_roles'] = $user->getRoleNames()->toArray();
                $data['permissions'] = Permission::select('id', 'name')->get();
                //                $data['assigned_permissions'] = $user->getPermissionNames()->toArray();
                $data['role_to_permissions'] = AssignPermissionToRole::where('user_id', $id)->get();

                return view('auth.create_user_role', $data);

            case 'assign_permissions':
                $data['role'] = $id;
                $data['permissions'] = Permission::get();
                $data['get_permissions'] = DB::table('role_has_permissions')->where('role_id', $id)->pluck('permission_id')->toArray();

                return view('admin.role.assign_permissions', $data);

            case 'dropdown':
                $data['category_id'] = $id;
                $data['dropdowns'] = Dropdown::where('lookup_code_id', $id)->get();

                return view('admin.dropdown.dropdowns', $data);

            case 'accommodations':
                $data['accommodation_id'] = $id;
                $data['accommodations'] = Accommodation::where('venue_id', $id)->get();

                return view('admin.accommodation.accommodation', $data);

            case 'blocks_setup':
                $data['resident'] = Accommodation::find($id);
                abort_if(! $data['resident'], 404, 'Accommodation not found.');

                $data['blocks'] = AccommodationBlock::where('residence_id', $id)->get();

                return view('admin.accommodation.resident.setup_block', $data);

            case 'generate_rooms':
                $data['block'] = AccommodationBlock::find($id);
                abort_if(! $data['block'], 404, 'Accommodation block not found.');

                $data['rooms'] = AccommodationRoom::where('block_id', $id)->get();

                return view('admin.accommodation.room.create', $data);

            case 'fees':
                $data['event_id'] = $id;
                $data['accommodation'] = EventFees::where([
                    'fee_type' => 'accommodation',
                    'event_id' => $id,
                ])->get();
                $data['registration'] = EventFees::where([
                    'fee_type' => 'registration_fee',
                    'event_id' => $id,
                ])->get();

                return view('admin.event.fees', $data);

            case 'financial_clearance':
                $data['payment'] = OnlinePayment::find($id);
                abort_if(! $data['payment'], 404, 'Payment not found.');

                $data['payment_made'] = OnlinePayment::where([
                    'reg_id' => $data['payment']->reg_id,
                    'event_id' => $data['payment']->event_id,
                ])->sum('amount_paid');

                return view('admin.finance.financial_clearance', $data);

            default:
                return 'No Form Selected';
        }
    }
}
