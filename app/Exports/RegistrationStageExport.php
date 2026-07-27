<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RegistrationStageExport implements FromCollection, WithHeadings, WithStyles
{
    /**
     * A static, fictional example row — not real registrant data. The
     * import (RegistrationStageImport::getLookup/getCountry) matches
     * dropdown/country columns by text via a LIKE search, so the example
     * values here are labels (e.g. "Male", "Ghana"), not raw IDs.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return new Collection([
            [
                'title' => 'Mr',
                'first_name' => 'John',
                'surname' => 'Doe',
                'other_names' => '',
                'date_of_birth' => '1990-01-01',
                'gender' => 'Male',
                'phone_number' => '+233500000000',
                'whatsapp_number' => '+233500000000',
                'marital_status' => 'Single',
                'nationality_id' => 'Ghana',
                'email' => 'john.doe@example.com',
                'address' => '123 Example Street',
                'position_held' => 'Member',
                'profession' => 'Engineer',
                'residence_country_id' => 'Ghana',
                'languages_spoken' => 'English',
                'need_accommodation' => 1,
                'emergency_contacts_name' => 'Jane Doe',
                'emergency_contacts_relationship' => 'Sister',
                'emergency_contacts_phone_number' => '+233500000001',
                'event_id' => '',
                'attendance_type' => 'In-Person',
                'disability' => 0,
                'special_needs' => 'None',
            ],
        ]);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function headings(): array
    {
        return [
            'title',
            'first_name',
            'surname',
            'other_names',
            'date_of_birth',
            'gender',
            'phone_number',
            'whatsapp_number',
            'marital_status',
            'nationality_id',
            'email',
            'address',
            'position_held',
            'profession',
            'residence_country_id',
            'languages_spoken',
            'need_accommodation',
            'emergency_contacts_name',
            'emergency_contacts_relationship',
            'emergency_contacts_phone_number',
            'event_id',
            'attendance_type',
            'disability',
            'special_needs',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
            //            2    => ['font' => ['bold' => true]],

            // Styling a specific cell by coordinate.
            //            'A1' => ['font' => ['size' => 16]],
            // 'B2' => ['font' => ['italic' => true]],

            // // Styling an entire column.
            // 'C'  => ['font' => ['size' => 16]],
        ];
    }
}
