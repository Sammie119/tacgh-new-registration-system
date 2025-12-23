<?php

use App\Models\Admin\Accommodation;
use App\Models\Admin\AccommodationBlock;
use App\Models\Admin\AccommodationRoom;
use App\Models\Admin\AssignedRoomEpisode;
use App\Models\Admin\AssignPermissionToRole;
use App\Models\Admin\Country;
use App\Models\Admin\Dropdown;
use App\Models\Admin\Event;
use App\Models\Admin\EventFees;
use App\Models\RegistrantStage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

if (!function_exists("get_logged_in_user_id")) {
    function get_logged_in_user_id(): int
    {
        return Auth::user()->id;
    }
}

if (!function_exists("get_logged_in_user_event_id")) {
    function get_logged_in_user_event_id(): int
    {
        return Auth::user()->event_id;
    }
}

if (!function_exists("get_user_name")) {
    function get_user_name($id): string
    {
        $user = User::find($id);
        if($user){
            return $user->name;
        }
        return "";
    }
}

if (!function_exists("get_permission_name")) {
    function get_permission_name($id): string
    {
        $permission = Permission::find($id);

        if($permission){
            return $permission->name;
        }
        return "";

    }
}

if (!function_exists("use_roles_sidebar")) {
    function use_roles_sidebar($role_name)
    {
        return auth()->user()->hasRole($role_name->value);

    }
}

if (!function_exists("get_assigned_role_to_permission")) {
    function get_assigned_role_to_permission($role_name)
    {
        $role = auth()->user()->hasRole($role_name->value);

        $get_role = Role::where('name', $role_name->value)->first();

        if($role){
            $get_assigned = AssignPermissionToRole::where(['user_id' => get_logged_in_user_id(), 'role_id' => $get_role->id])->first();

            $get_permission = Permission::find($get_assigned->permission_id)->name === 'Write';
        } else {
            $get_permission = $get_role->hasPermissionTo('Write');
        }

        return $get_permission;
    }
}

if(!function_exists('get_active_flag')) {
    function get_active_flag($active_flag)
    {
        if($active_flag === 1 ) {
            return '<span class="badge rounded-pill bg-success">Enabled</span>';
        }

        return '<span class="badge rounded-pill bg-danger">Disabled</span>';
    }
}

if (!function_exists("get_dropdown_name")) {
    function get_dropdown_name($id): string|null
    {
        $dropdown = Dropdown::find($id);
        if($dropdown){
            return $dropdown->full_name;
        }
        return null;
    }
}

if (!function_exists("get_gender")) {
    function get_gender($gender)
    {
        if ($gender === "M")
            return 'Male';
        elseif ($gender === "F")
            return 'Female';
        else
            return 'Mixed';
    }
}

if (!function_exists("get_registrant_login")) {
    function get_registrant_login()
    {
        return Session::get('registrant');
    }
}

if (!function_exists("get_country")) {
    function get_country($id): string|null
    {
        $result = Country::find($id);
        if($result){
            return $result->name;
        }
        return null;
    }
}

if (!function_exists("get_event")) {
    function get_event($id)
    {
        $result = Event::find($id);
        if($result){
            return $result;
        }
        return null;
    }
}

if(!function_exists("event_registration_code")){
    function event_registration_code ($input, $pad_len = 7, $prefix = null, $subfix = null) {
        if (is_string($prefix))
            return sprintf("%s%s", $prefix, str_pad($input, $pad_len, "0", STR_PAD_LEFT));

        return str_pad($input, $pad_len, "0", STR_PAD_LEFT);
    }
}

if(!function_exists("event_registrant_name")){
    function event_registrant_name ($id) {

        $reg = RegistrantStage::find($id);
        if($reg){
            $name = get_dropdown_name($reg->title). ' '. $reg->first_name. ' '. $reg->other_names. ' '. $reg->surname;
            return strtoupper($name);
        }
        return null;

    }
}

if(!function_exists("get_registration_type")){
    function get_registration_type ($id) {

        $reg = EventFees::find($id);
        if($reg){
            return $reg->description;
        }
        return null;

    }
}

if(!function_exists("get_total_room_occupants")){
    function get_total_room_occupants ($room_id, $event_id) {
        return AssignedRoomEpisode::where(['event_id' => $event_id, 'room_id' => $room_id])->count();
    }
}

if(!function_exists("get_room_number")){
    function get_room_number ($room_id) {
        $room = AccommodationRoom::find($room_id);
        if($room){
            $residence = Accommodation::find($room->residence_id);

            $block = AccommodationBlock::find($room->block_id);

            $roomName = $room->prefix."".$room->room_no."".$room->suffix;
            $resName = $residence->name;
            $blockName = $block->name;

            return "$roomName in $resName, $blockName";
        }

        return null;

    }
}

if(!function_exists("event_registrant_age")){
    function event_registrant_age($id) {

        $reg = DB::table('vw_registration')->where('stage_id', $id)->first();
        if ($reg) {
            return $reg->age;
        }
        return null;

    }
}
