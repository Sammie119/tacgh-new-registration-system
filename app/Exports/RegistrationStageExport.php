<?php

namespace App\Exports;

use App\Models\RegistrantStage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RegistrationStageExport implements FromCollection, WithHeadings, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return RegistrantStage::select(
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
                'special_needs'
        )->limit(1)->get();
//        return User::select("id", "name", "email")->get();
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
            'special_needs'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
//            2    => ['font' => ['bold' => true]],

            // Styling a specific cell by coordinate.
//            'A1' => ['font' => ['size' => 16]],
            // 'B2' => ['font' => ['italic' => true]],

            // // Styling an entire column.
            // 'C'  => ['font' => ['size' => 16]],
        ];
    }
}
