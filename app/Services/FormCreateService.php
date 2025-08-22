<?php

namespace App\Services;

use App\Helpers\Utils;
use App\Models\Admin\Dropdown;
use App\Models\Admin\EventVenue;

class FormCreateService
{
    static public function create($type)
    {
        switch ($type) {
            case 'user':
                $data['person_type'] = [];
                return view('auth.create', $data);

            case 'permission':
                return view('admin.permission.create');

            case 'role':
                return view('admin.role.create');

            case 'dropdown_category':
                return view('admin.dropdown.create');

            case 'venue':
                $data['regions'] = Utils::getLookups(4);
                return view('admin.accommodation.create', $data);

            case 'event':
                $data['venues'] = EventVenue::orderBy('name')->get();
                return view('admin.event.create', $data);

            case 'forms':
                return view('admin.forms.create');

            case 'financial_entry':
                $data['transaction_types'] = Utils::getLookups(23);
                return view('admin.finance.create', $data);

            case 'downloads':
                return view('admin.downloads.create');

            default:
                return "No Form Selected";
        }
    }
}
