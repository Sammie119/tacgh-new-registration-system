<?php

namespace App\Services\Admin;

use App\Models\Admin\Dropdown;
use App\Models\RegistrantStage;
use Carbon\Carbon;

class ReportService
{
    public function demographics($eventId)
    {
        $stages = RegistrantStage::where('event_id', $eventId)
            ->get(['gender', 'date_of_birth', 'nationality_id', 'residence_country_id', 'attendance_type', 'confirmed']);

        $data['total'] = $stages->count();

        $data['gender_counts'] = $stages->countBy('gender');
        $data['nationality_counts'] = $stages->countBy('nationality_id');
        $data['residence_counts'] = $stages->countBy('residence_country_id');

        $lookupIds = $data['gender_counts']->keys()
            ->merge($data['nationality_counts']->keys())
            ->merge($data['residence_counts']->keys())
            ->filter()
            ->unique();
        $data['dropdown_names'] = Dropdown::whereIn('id', $lookupIds)->pluck('full_name', 'id');

        $data['attendance_counts'] = $stages->countBy('attendance_type');
        $data['confirmed_counts'] = $stages->countBy('confirmed');

        $brackets = ['Under 18' => 0, '18-30' => 0, '31-50' => 0, '51+' => 0, 'Unknown' => 0];
        foreach ($stages as $stage) {
            if (empty($stage->date_of_birth)) {
                $brackets['Unknown']++;

                continue;
            }

            $age = Carbon::parse($stage->date_of_birth)->age;
            match (true) {
                $age < 18 => $brackets['Under 18']++,
                $age <= 30 => $brackets['18-30']++,
                $age <= 50 => $brackets['31-50']++,
                default => $brackets['51+']++,
            };
        }
        $data['age_brackets'] = $brackets;

        return view('admin.reports.demographics', $data);
    }
}
