<?php

namespace App\Services\Admin;

use App\Models\Admin\Country;
use App\Models\Admin\Dropdown;
use App\Models\Admin\EventFees;
use App\Models\BatchLog;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use Carbon\Carbon;

class ReportService
{
    public function demographics($eventId)
    {
        $stages = RegistrantStage::where('event_id', $eventId)
            ->get(['id', 'gender', 'date_of_birth', 'nationality_id', 'residence_country_id', 'attendance_type', 'confirmed', 'profession', 'position_held', 'marital_status']);

        $data['total'] = $stages->count();

        $data['gender_counts'] = $stages->countBy('gender');
        $data['nationality_counts'] = $stages->countBy('nationality_id');
        $data['residence_counts'] = $stages->countBy('residence_country_id');
        $data['position_counts'] = $stages->countBy('position_held');
        $data['marital_status_counts'] = $stages->countBy('marital_status');

        // The registration form only ever writes lookup_code_id=10 (Profession)
        // rows into `profession`, but the batch-import path used to resolve
        // this field by a fuzzy full_name LIKE match with no lookup_code_id
        // filter (RegistrationStageImport::getLookup(), fixed going forward
        // but not backfilled), so real data also contains stray rows from
        // other dropdown categories. Only count a row under Profession if it
        // actually belongs to that category - do not just countBy('profession')
        // directly.
        $professionIds = $stages->pluck('profession')->filter()->unique();
        $professionDropdowns = Dropdown::whereIn('id', $professionIds)->get(['id', 'lookup_code_id', 'full_name'])->keyBy('id');

        $professionStages = $stages->filter(
            fn ($stage) => (int) ($professionDropdowns->get($stage->profession)?->lookup_code_id) === 10
        );
        $data['profession_counts'] = $professionStages->countBy('profession');

        // gender, position_held, and marital_status are all genuine
        // dropdowns/lookups rows (unlike nationality/residence, see below).
        // profession names are already fetched above in $professionDropdowns
        // - reuse rather than querying Dropdown again.
        $dropdownIds = $data['gender_counts']->keys()
            ->merge($data['position_counts']->keys())
            ->merge($data['marital_status_counts']->keys())
            ->filter()
            ->unique();
        // union(), not merge() - merge() uses array_merge() semantics, which
        // renumbers integer keys (dropdown IDs) instead of preserving them.
        $data['dropdown_names'] = Dropdown::whereIn('id', $dropdownIds)->pluck('full_name', 'id')
            ->union($professionDropdowns->pluck('full_name', 'id'));

        // Accommodation Type and Registration Fee Type both come from which
        // EventFees package each Registrant actually selected
        // (Registrant.accommodation_type / Registrant.registration_type),
        // not from anything derived from the profession column/dropdowns
        // table - a Dropdown category happens to share the internal name
        // "Registration Type" but its content doesn't reflect real selections.
        // Scope to stage_ids that genuinely belong to this event's stages,
        // not just event_id, so a Registrant row left orphaned by a stage
        // that was hard-deleted outside the normal removal flow doesn't
        // inflate these counts past the real Total Registrants figure.
        $registrants = Registrant::where('event_id', $eventId)
            ->whereIn('stage_id', $stages->pluck('id'))
            ->get(['registration_type', 'accommodation_type']);
        $data['registration_fee_type_counts'] = $registrants->countBy('registration_type');
        $data['accommodation_type_counts'] = $registrants->countBy('accommodation_type');
        $feeTypeIds = $data['registration_fee_type_counts']->keys()
            ->merge($data['accommodation_type_counts']->keys())
            ->filter()
            ->unique();
        $data['fee_type_names'] = EventFees::whereIn('id', $feeTypeIds)->pluck('description', 'id');

        // nationality_id / residence_country_id reference the countries
        // table, not the generic dropdowns/lookups table used for gender etc.
        $countryIds = $data['nationality_counts']->keys()
            ->merge($data['residence_counts']->keys())
            ->filter()
            ->unique();
        $data['country_names'] = Country::whereIn('id', $countryIds)->pluck('name', 'id');

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

    public function tokens($eventId, ?string $individualSearch = null, ?string $batchSearch = null, ?string $memberSearch = null)
    {
        $individualQuery = RegistrantStage::where('event_id', $eventId)->where('batch_no', 0)->orderByDesc('id');
        if (! empty($individualSearch)) {
            $individualQuery->where(function ($q) use ($individualSearch) {
                $q->where('first_name', 'like', "%{$individualSearch}%")
                    ->orWhere('surname', 'like', "%{$individualSearch}%")
                    ->orWhere('other_names', 'like', "%{$individualSearch}%")
                    ->orWhere('phone_number', 'like', "%{$individualSearch}%")
                    ->orWhere('email', 'like', "%{$individualSearch}%")
                    ->orWhere('token', 'like', "%{$individualSearch}%");
            });
        }
        $data['individuals'] = $individualQuery->paginate(50, ['*'], 'individual_page')->withQueryString();

        $batchQuery = BatchLog::where('event_id', $eventId)->orderByDesc('id');
        if (! empty($batchSearch)) {
            $batchQuery->where(function ($q) use ($batchSearch) {
                $q->where('email', 'like', "%{$batchSearch}%")
                    ->orWhere('phone_number', 'like', "%{$batchSearch}%")
                    ->orWhere('whatsapp_number', 'like', "%{$batchSearch}%")
                    ->orWhere('token', 'like', "%{$batchSearch}%")
                    ->orWhere('batch_no', 'like', "%{$batchSearch}%");
            });
        }
        $data['batches'] = $batchQuery->paginate(50, ['*'], 'batch_page')->withQueryString();

        $memberQuery = RegistrantStage::where('event_id', $eventId)->where('batch_no', '!=', 0)->orderByDesc('id');
        if (! empty($memberSearch)) {
            $memberQuery->where(function ($q) use ($memberSearch) {
                $q->where('first_name', 'like', "%{$memberSearch}%")
                    ->orWhere('surname', 'like', "%{$memberSearch}%")
                    ->orWhere('other_names', 'like', "%{$memberSearch}%")
                    ->orWhere('phone_number', 'like', "%{$memberSearch}%")
                    ->orWhere('email', 'like', "%{$memberSearch}%")
                    ->orWhere('token', 'like', "%{$memberSearch}%")
                    ->orWhere('batch_no', 'like', "%{$memberSearch}%");
            });
        }
        $data['members'] = $memberQuery->paginate(50, ['*'], 'member_page')->withQueryString();

        $data['individual_search'] = $individualSearch;
        $data['batch_search'] = $batchSearch;
        $data['member_search'] = $memberSearch;

        $titleIds = $data['individuals']->pluck('title')->merge($data['members']->pluck('title'))->filter()->unique();
        $data['dropdown_names'] = Dropdown::whereIn('id', $titleIds)->pluck('full_name', 'id');

        $batchNos = $data['batches']->pluck('batch_no');
        $data['member_counts'] = RegistrantStage::whereIn('batch_no', $batchNos)
            ->where('batch_no', '!=', 0)
            ->selectRaw('batch_no, COUNT(*) as cnt')
            ->groupBy('batch_no')
            ->get()
            ->pluck('cnt', 'batch_no');

        return view('admin.reports.tokens', $data);
    }
}
