<?php

namespace App\Services;

use App\Helpers\Utils;
use App\Models\Admin\Accommodation;
use App\Models\Admin\Dropdown;
use App\Models\Admin\DropdownCategory;
use App\Models\Admin\Event;
use App\Models\Admin\EventVenue;
use App\Models\Admin\Form;
use App\Models\Admin\FormField;
use App\Models\FinancialEpisode;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FormEditService
{
    static public function edit($type, $id)
    {
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

            default:
                return "No Form Selected";
        }
    }
}
