<?php

namespace App\Pipelines\Registration;

use App\Models\RegistrantStage;

class ConfirmationPipe
{
    public function handle(array $data, \Closure $next)
    {
        RegistrantStage::find($data['id'])->update([
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'phone_number' => $data['phone_number'],
            'event_id' => $data['event_id'],
            'title' => $data['title'],
            'first_name' => $data['first_name'],
            'surname' => $data['surname'],
            'other_names' => $data['other_names'],
            'marital_status' => $data['marital_status'],
            'nationality_id' => $data['nationality_id'],
            'whatsapp_number' => $data['whatsapp_number'],
            'email' => $data['email'],
            'address' => $data['address'],
            'position_held' => $data['position_held'],
            'profession' => $data['profession'],
            'residence_country_id' => $data['residence_country_id'],
            'languages_spoken' => $data['languages_spoken'],
            'need_accommodation' => $data['need_accommodation'],
            'emergency_contacts_name' => $data['emergency_contacts_name'],
            'emergency_contacts_relationship' => $data['emergency_contacts_relationship'],
            'emergency_contacts_phone_number' => $data['emergency_contacts_phone_number'],
            'attendance_type' => $data['attendance_type'],
            'disability' => $data['disability'],
            'special_needs' => $data['special_needs'],
            'confirmed' => 'Yes'
        ]);

        return $next($data);
    }
}
