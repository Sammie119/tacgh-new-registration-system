<?php

namespace App\Pipelines\Registration;

use App\Helpers\Utils;
use App\Models\Registrant;
use Illuminate\Support\Facades\DB;

class RegistrantPipe
{
    public function handle(array $data, \Closure $next)
    {
        $ref_date = date('y');
        $event = get_event($data['event_id']);
        abort_if(! $event, 404, 'Event not found.');
        $prefix = $event->code_prefix;

        $fees = [
            'accommodation_type' => $data['accommodation_fee'],
            'accommodation_fee' => Utils::eventRegistrationFee($data['accommodation_fee']),
            'registration_type' => $data['registration_fee'],
            'registration_fee' => Utils::eventRegistrationFee($data['registration_fee']),
            'total_fee' => Utils::eventRegistrationFee($data['accommodation_fee']) + Utils::eventRegistrationFee($data['registration_fee']),
        ];

        $registrant = DB::transaction(function () use ($data, $prefix, $ref_date, $fees) {
            // Lock the matching row (if any) for the duration of the
            // transaction so concurrent confirmations for the same event
            // can't both read the same "next number" below.
            $existing = Registrant::where([
                'stage_id' => $data['id'],
                'event_id' => $data['event_id'],
            ])->lockForUpdate()->first();

            if ($existing) {
                // Registration number is assigned once and stays stable
                // across re-confirmation; only the fee/amount fields refresh.
                $existing->update($fees);

                return $existing;
            }

            $nextNumber = Registrant::where('event_id', $data['event_id'])->lockForUpdate()->count() + 1;

            return Registrant::create(array_merge([
                'stage_id' => $data['id'],
                'event_id' => $data['event_id'],
                'registration_no' => event_registration_code($nextNumber, 4, "$prefix-$ref_date-"),
            ], $fees));
        });

        // total_fee here is whatever was just persisted above (server-
        // computed from the selected fee IDs) - PaymentPipe/makePayment()
        // re-derives the amount to charge from the DB anyway, so this must
        // not be overwritten with the client-submitted amount_to_pay field.
        return $next($registrant->toArray());
    }
}
