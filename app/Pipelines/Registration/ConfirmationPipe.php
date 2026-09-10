<?php

namespace App\Pipelines\Registration;

use App\Helpers\Utils;
use App\Models\RegistrantStage;

class ConfirmationPipe
{
    public function handle(array $data, \Closure $next)
    {
        $stage = RegistrantStage::find($data['id']);
        abort_if(! $stage, 404, 'Registrant not found.');

        // Event Attending can never change after initial registration, and
        // the individual confirmation form no longer submits it at all -
        // always trust the stored value over the form (RegistrantPipe,
        // next in this pipeline, reads $data['event_id'] directly).
        $data['event_id'] = $stage->event_id;

        $data['phone_number'] = Utils::normalizeGhanaPhone($data['phone_number']);
        // WhatsApp Number was dropped from the individual forms - default
        // it to the phone number, same as the batch import does.
        $data['whatsapp_number'] = Utils::normalizeGhanaPhone($data['whatsapp_number'] ?? $data['phone_number']);
        $data['emergency_contacts_phone_number'] = Utils::normalizeGhanaPhone($data['emergency_contacts_phone_number']);

        $updates = [
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'phone_number' => $data['phone_number'],
            'title' => $data['title'],
            'first_name' => $data['first_name'],
            'surname' => $data['surname'],
            'marital_status' => $data['marital_status'],
            'nationality_id' => $data['nationality_id'],
            'whatsapp_number' => $data['whatsapp_number'],
            'email' => $data['email'],
            'profession' => $data['profession'],
            'residence_country_id' => $data['residence_country_id'],
            'languages_spoken' => $data['languages_spoken'],
            'need_accommodation' => $data['need_accommodation'],
            'emergency_contacts_name' => $data['emergency_contacts_name'],
            'emergency_contacts_phone_number' => $data['emergency_contacts_phone_number'],
            'disability' => $data['disability'],
            'special_needs' => $data['special_needs'],
            'confirmed' => 'Yes',
        ];

        // Other Names, Address, Position Held, Emergency Contact
        // Relationship, and Attendance Type were dropped from the
        // individual forms (set once at initial sign-up instead), but the
        // batch confirmation form still submits all of them - only
        // overwrite here when present, so batch confirmation keeps
        // overwriting them and individual confirmation leaves the
        // initial-registration values untouched.
        foreach (['other_names', 'address', 'position_held', 'emergency_contacts_relationship', 'attendance_type'] as $field) {
            if (array_key_exists($field, $data)) {
                $updates[$field] = $data[$field];
            }
        }

        // Is Student / Institution Name only exist on the individual
        // confirmation form (batch doesn't submit them at all).
        if (array_key_exists('is_student', $data)) {
            $updates['is_student'] = $data['is_student'];
            $updates['institution_name'] = $data['is_student'] ? ($data['institution_name'] ?? null) : null;
        }

        $stage->update($updates);

        return $next($data);
    }
}
