<?php

namespace App\Imports;

use App\Helpers\Utils;
use App\Models\Admin\Country;
use App\Models\Admin\Dropdown;
use App\Models\RegistrantStage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class RegistrationStageImport implements ToModel,WithHeadingRow, WithValidation
{
    private $event_id;
    private $batch_no;
    public function __construct($event_id, $batch_no)
    {
        $this->event_id = $event_id;
        $this->batch_no = $batch_no;
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    private function dateConvertor($date): string
    {
        if(is_int($date)){
            return date("Y-m-d", $date);
        }
        return date('Y-m-d', strtotime($date));
    }

    private function getLookup($name): int
    {
        $id = Dropdown::whereRaw("full_name LIKE '%$name%'")->first();
        if($id != null){
            return $id->id;
        }
        return 0;
    }

    private function getCountry($name): int
    {
        $id = Country::whereRaw("name LIKE '%$name%'")->first();
        if($id != null){
            return $id->id;
        }
        return 0;
    }

    public function model(array $row)
    {
        $token = Utils::generateToken(6);

        return new RegistrantStage([
            'title' => $this->getLookup($row['title']),
            'first_name' => $row['first_name'],
            'surname' => $row['surname'],
            'other_names' => $row['other_names'],
            'marital_status' => $this->getLookup($row['marital_status']),
            'nationality_id' => $this->getCountry($row['nationality_id']),
            'whatsapp_number' => $row['whatsapp_number'],
            'date_of_birth' => $this->dateConvertor($row['date_of_birth']),
            'gender' => $this->getLookup($row['gender']),
            'phone_number' => $row['phone_number'],
            'event_id' => $this->event_id,
            'email' => $row['email'],
            'address' => $row['address'],
            'position_held' => $this->getLookup($row['position_held']),
            'profession' => $this->getLookup($row['profession']),
            'residence_country_id' => $this->getCountry($row['residence_country_id']),
            'languages_spoken' => $row['languages_spoken'],
            'need_accommodation' => $row['need_accommodation'],
            'emergency_contacts_name' => $row['emergency_contacts_name'],
            'emergency_contacts_relationship' => $row['emergency_contacts_relationship'],
            'emergency_contacts_phone_number' => $row['emergency_contacts_phone_number'],
            'attendance_type' => $row['attendance_type'],
            'disability' => $row['disability'],
            'special_needs' => $row['special_needs'],
            'token' => $token,
            'batch_no' => $this->batch_no,
        ]);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function rules(): array
    {
        return [
            'title' => 'required',
            'first_name' => 'required',
            'surname' => 'required',
            'other_names' => 'nullable',
            'gender' => 'required',
            'date_of_birth' => 'required|date',
            'marital_status' => 'required',
            'nationality_id' => 'required',
            'phone_number' => 'required|regex:/^\+[1-9][0-9]{10,}$/',
            'whatsapp_number' => 'nullable|regex:/^\+[1-9][0-9]{10,}$/',
            'email' => 'required|email',
            'address' => 'required',
            'position_held' => 'required',
            'profession' => 'required',
            'residence_country_id' => 'required',
            'languages_spoken' => 'required',
            'need_accommodation' => 'required|boolean',
            'emergency_contacts_name' => 'required',
            'emergency_contacts_relationship' => 'required',
            'emergency_contacts_phone_number' => 'required|regex:/^\+[1-9][0-9]{10,}$/',
            'attendance_type' => 'required|in:In-Person,Online',
            'event_id' => 'required|exists:events,id',
            'disability' => 'required|boolean',
            'special_needs' => 'required',
        ];
    }
}
