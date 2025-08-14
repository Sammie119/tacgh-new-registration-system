<?php

namespace App\Services\Admin;

use App\Models\Admin\Accommodation;
use App\Models\Admin\AccommodationBlock;
use App\Models\Admin\AccommodationRoom;
use App\Models\Admin\EventVenue;

class VenueService
{
    public function index()
    {
        $data['venues'] = EventVenue::orderByDesc('id')->get();
        return view('admin.accommodation.index', $data);
    }

    public function store(array $data)
    {
        $results = EventVenue::firstOrCreate([
                'name' => trim($data['name']),
                'region_id' => trim($data['region_id']),
            ],
            [
                'location' => trim($data['location']),
                'active_flag' => isset($data['active_flag']) ? 1 : 0,
                'created_by' => get_logged_in_user_id(),
                'updated_by' => get_logged_in_user_id(),
            ]);

        if($results){
            return redirect(route('venues', absolute: false))->with('success', 'Venue Created Successfully.');
        }
        return redirect(route('venues', absolute: false))->with('error', 'Venue Creation Unsuccessful!!!');
    }

    public function update(array $data)
    {
        $results = EventVenue::find($data['id'])->update([
                'name' => trim($data['name']),
                'region_id' => trim($data['region_id']),
                'location' => trim($data['location']),
                'active_flag' => isset($data['active_flag']) ? 1 : 0,
                'updated_by' => get_logged_in_user_id(),
            ]);

        if($results){
            return redirect(route('venues', absolute: false))->with('success', 'Venue Updated Successfully.');
        }
        return redirect(route('venues', absolute: false))->with('error', 'Venue Update Unsuccessful!!!');
    }

    static public function destroy($id)
    {
        $record = EventVenue::find($id);
        if($record){
            $record->delete();
            return 1;
        }
        return 0;
    }
}
