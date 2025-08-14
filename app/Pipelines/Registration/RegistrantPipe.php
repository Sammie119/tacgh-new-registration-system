<?php

namespace App\Pipelines\Registration;

use App\Helpers\Utils;
use App\Models\Registrant;
use Illuminate\Support\Facades\DB;

class RegistrantPipe
{
    public function handle(array $data, \Closure $next)
    {
        $amount_to_pay = $data['amount_to_pay'];
        $currentYear = date('Y');
        $count = DB::table('registrants')->whereRaw("YEAR(created_at) = $currentYear")
            ->where('event_id', $data['event_id'])->count();
        $ref_date = date("y");
        $prefix = get_event($data['event_id'])->code_prefix;

        $data = Registrant::create([
            'registration_no' => event_registration_code(++$count, 4, "$prefix-$ref_date-"),
            'stage_id' => $data['id'],
            'event_id' => $data['event_id'],
            'accommodation_type' => $data['accommodation_fee'],
            'accommodation_fee' => Utils::eventRegistrationFee($data['accommodation_fee']),
            'registration_type' => $data['registration_fee'],
            'registration_fee' => Utils::eventRegistrationFee($data['registration_fee']),
            'total_fee' => Utils::eventRegistrationFee($data['accommodation_fee']) + Utils::eventRegistrationFee($data['registration_fee']),
//            'room_no' => $data['room_no'],
//            'check_in' => $data['check_in'],
//            'check_out' => $data['check_out'],
        ]);

        $data->total_fee = (floatval($amount_to_pay));

        return $next($data->toArray());
    }
}
