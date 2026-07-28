<?php

namespace App\Services;

use App\Enums\RolesEnum;
use App\Helpers\Utils;
use App\Models\Admin\Accommodation;
use App\Models\Admin\Download;
use App\Models\Admin\DropdownCategory;
use App\Models\Admin\Event;
use App\Models\Admin\EventVenue;
use App\Models\Admin\Form;
use App\Models\FinancialEpisode;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FormEditService
{
    /**
     * Roles allowed to edit each resource type, mirroring the role
     * middleware already applied to that resource's create/update routes.
     */
    private const ROLES_ALLOWED_TO_EDIT = [
        'user' => [RolesEnum::SYSTEMADMIN],
        'permission' => [RolesEnum::SYSTEMDEVELOPER],
        'role' => [RolesEnum::SYSTEMDEVELOPER],
        'dropdown_category' => [RolesEnum::SYSTEMADMIN, RolesEnum::SUPERADMIN],
        'venue' => [RolesEnum::SYSTEMADMIN, RolesEnum::SUPERADMIN],
        'event' => [RolesEnum::SYSTEMADMIN, RolesEnum::SUPERADMIN],
        'resident' => [RolesEnum::SYSTEMADMIN, RolesEnum::ROOMALLOCATOR, RolesEnum::SUPERADMIN],
        'forms' => [RolesEnum::SYSTEMADMIN, RolesEnum::SUPERADMIN],
        'financial_entry' => [RolesEnum::SYSTEMADMIN, RolesEnum::FINANCE, RolesEnum::SUPERADMIN],
        'downloads' => [RolesEnum::SYSTEMADMIN, RolesEnum::SUPERADMIN],
    ];

    public static function edit($type, $id)
    {
        FormAuthorization::guard($type, self::ROLES_ALLOWED_TO_EDIT);

        switch ($type) {
            case 'user':
                $data['user'] = User::find($id);

                return view('auth.create', $data);

            case 'permission':
                $data['permission'] = Permission::find($id);

                return view('admin.permission.create', $data);

            case 'role':
                $data['role'] = Role::find($id);

                return view('admin.role.create', $data);

            case 'dropdown_category':
                $data['category'] = DropdownCategory::find($id);

                return view('admin.dropdown.create', $data);

            case 'venue':
                $data['venue'] = EventVenue::find($id);
                $data['regions'] = Utils::getLookups(4);

                return view('admin.accommodation.create', $data);

            case 'event':
                $data['event'] = Event::find($id);
                $data['venues'] = EventVenue::orderBy('name')->get();

                return view('admin.event.create', $data);

            case 'resident':
                $data['resident'] = Accommodation::find($id);

                return view('admin.accommodation.resident.create', $data);

            case 'forms':
                $data['form'] = Form::find($id);

                return view('admin.forms.create', $data);

            case 'financial_entry':
                $data['financial_entry'] = FinancialEpisode::find($id);
                $data['transaction_types'] = Utils::getLookups(23);

                return view('admin.finance.create', $data);

            case 'downloads':
                $data['download'] = Download::find($id);

                return view('admin.downloads.create', $data);

            default:
                return 'No Form Selected';
        }
    }
}
