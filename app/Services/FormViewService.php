<?php

namespace App\Services;

use App\Models\Admin\Accommodation;
use App\Models\Admin\AccommodationBlock;
use App\Models\Admin\AccommodationRoom;
use App\Models\Admin\AssignPermissionToRole;
use App\Models\Admin\Dropdown;
use App\Models\Admin\EventFees;
use App\Models\Admin\OnlinePayment;
use App\Models\Registrant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FormViewService
{
    static public function view($type, $id)
    {
        switch ($type) {
            case 'user_roles':
                $user = User::find($id);
                $data['user'] = $user->id;
                $data['roles'] = Role::select('id','name')->get();
                $data['assigned_roles'] = $user->getRoleNames()->toArray();
                $data['permissions'] = Permission::select('id','name')->get();
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
                $data['blocks'] = AccommodationBlock::where('residence_id', $id)->get();
                return view('admin.accommodation.resident.setup_block', $data);

            case 'generate_rooms':
                $data['block'] = AccommodationBlock::find($id);
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
                $data['payment_made'] = OnlinePayment::where([
                        'reg_id' => $data['payment']->reg_id,
                        'event_id' => $data['payment']->event_id,
                    ])->sum('amount_paid');
                return view('admin.finance.financial_clearance', $data);

            default:
                return "No Form Selected";
        }
    }
}
