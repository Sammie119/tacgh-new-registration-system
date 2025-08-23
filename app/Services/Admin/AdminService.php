<?php

namespace App\Services\Admin;

use App\Models\Admin\Accommodation;
use App\Models\Admin\AccommodationRoom;
use App\Models\Admin\AssignedRoomEpisode;
use App\Models\Admin\Download;
use App\Models\Admin\Event;
use App\Models\Admin\OnlinePayment;
use App\Models\FinancialEpisode;
use App\Models\RegistrantStage;

class AdminService
{
    public function index()
    {   $event = Event::find(get_logged_in_user_event_id());
        $data['downloads'] = Download::where(['event_id' => get_logged_in_user_event_id(), 'active_flag' => 1])->get();
        $data['reg_stage'] = RegistrantStage::where(['event_id' => get_logged_in_user_event_id()])->count();
        $data['confirmed'] = RegistrantStage::where(['event_id' => get_logged_in_user_event_id(), 'confirmed' => 'Yes'])->count();
        $data['payments'] = OnlinePayment::where(['event_id' => get_logged_in_user_event_id()])->sum('amount_paid');
        $residence_id = Accommodation::where('venue_id', $event->venue_id)->pluck('id')->toArray();
        $data['total_beds'] = AccommodationRoom::whereIn('residence_id', $residence_id)->sum('total_occupants');
        $data['beds_occupied'] = AssignedRoomEpisode::where(['event_id' => get_logged_in_user_event_id()])->count();
        $data['total_males'] = RegistrantStage::where([
                'event_id' => get_logged_in_user_event_id(),
                'confirmed' => 'Yes',
                'gender' => 3
            ])->count();
        $data['revenue'] = FinancialEpisode::where([
                'event_id' => get_logged_in_user_event_id(),
                'entry_type' => 'Income'
            ])->sum('amount');
        return view('admin.dashboard', $data);
    }
}
