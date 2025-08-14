<?php

namespace App\Services\Admin;

use App\Models\Admin\EventFees;

class EventFeesService
{
    public function store(array $data)
    {
        $results = 0;
        foreach ($data['fees'] as $value) {
//            dd($value, isset($value['active_flag']));
            if(empty($value['id'])){
                $results = EventFees::updateOrCreate([
                        'event_id' => $data['event_id'],
                        'fee_type' => trim($data['fee_type']),
                        'description' => trim($value['description']),
                        'fee_amount' => $value['fee_amount'],
                    ],
                    [
                        'active_flag' => isset($value['active_flag']) ? 1 : 0,
                        'created_by' => get_logged_in_user_id(),
                        'updated_by' =>  get_logged_in_user_id(),
                    ]);
            } else {
                $results = EventFees::find($value['id'])->update([
                    'event_id' => $data['event_id'],
                    'fee_type' => trim($data['fee_type']),
                    'description' => trim($value['description']),
                    'fee_amount' => $value['fee_amount'],
                    'active_flag' => isset($value['active_flag']) ? 1 : 0,
                    'updated_by' =>  get_logged_in_user_id(),
                ]);
            }
        }


        if($results){
            return redirect(route('events', absolute: false))->with('success', 'Event Free Created Successfully!!!');
        }

        return redirect(route('events', absolute: false))->with('error', 'Event Free Creation Unsuccessful!!!');
    }

    static public function destroy($id)
    {
        $record = EventFees::find($id);
        if($record){
            $record->delete();
            return 1;
        }
        return 0;
    }
}
