<?php

namespace App\Services\Admin;

use App\Models\Admin\AccommodationEpisode;
use App\Models\Admin\Event;

class EventService
{
    public function index()
    {
        $data['events'] = Event::orderByDesc('id')->get();
        return view('admin.event.index', $data);
    }

    public function store(array $data)
    {
        $results = Event::firstOrCreate([
                'name' => trim($data['name']),
                'description' => trim($data['description']),
                'code_prefix' => trim(strtoupper($data['code_prefix'])),
                'start_date' => trim($data['start_date']),
                'end_date' => trim($data['end_date']),
                'venue_id' => $data['venue_id'],
            ],
            [
                'is_payment_required' => isset($data['is_payment_required']) ? "Yes" : "No",
                'active_flag' => isset($data['active_flag']) ? 1 : 0,
                'created_by' => get_logged_in_user_id(),
                'updated_by' =>  get_logged_in_user_id(),
        ]);


        if($results){
            return redirect(route('events', absolute: false))->with('success', 'Events Created Successfully!!!');
        }

        return redirect(route('events', absolute: false))->with('error', 'Events Creation Unsuccessful!!!');
    }

    public function update(array $data)
    {
        $results = Event::find($data['id'])->update([
                'name' => trim($data['name']),
                'description' => trim($data['description']),
                'code_prefix' => trim(strtoupper($data['code_prefix'])),
                'start_date' => trim($data['start_date']),
                'end_date' => trim($data['end_date']),
                'is_payment_required' => isset($data['is_payment_required']) ? "Yes" : "No",
                'venue_id' => $data['venue_id'],
                'status' => trim($data['status']),
                'active_flag' => isset($data['active_flag']) ? 1 : 0,
                'updated_by' =>  get_logged_in_user_id(),
            ]);


        if($results){
            return redirect(route('events', absolute: false))->with('success', 'Events Updated Successfully!!!');
        }

        return redirect(route('events', absolute: false))->with('error', 'Events Update Unsuccessful!!!');
    }

    static public function destroy($id)
    {
        $record = Event::find($id);
        if($record){
            $record->delete();
            return 1;
        }
        return 0;
    }
}
