<?php

namespace App\Exports;

use App\Models\Admin\Dropdown;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RegistrationStageExport implements FromCollection, WithEvents, WithHeadings, WithStrictNullComparison, WithStyles
{
    /**
     * Excel column letter for each dropdown-backed field, matching the
     * order in headings() - needed to target data validation at the
     * right column in registerEvents().
     */
    private const DROPDOWN_COLUMNS = [
        'title' => ['column' => 'A', 'lookup_code_id' => 22],
        'gender' => ['column' => 'F', 'lookup_code_id' => 2],
        'marital_status' => ['column' => 'I', 'lookup_code_id' => 3],
        'position_held' => ['column' => 'M', 'lookup_code_id' => 5],
        'profession' => ['column' => 'N', 'lookup_code_id' => 10],
    ];

    private const ATTENDANCE_TYPE_COLUMN = 'U';

    private const BOOLEAN_COLUMNS = ['Q', 'V']; // Need Accommodation, Disability

    private const NATIONALITY_COLUMN = 'J';

    private const RESIDENCE_COUNTRY_COLUMN = 'O';

    private const LAST_EXAMPLE_ROW = 201; // generous allowance for a real batch upload

    /**
     * A static, fictional example row — not real registrant data. The
     * import (RegistrationStageImport::getLookup/getCountry) matches
     * dropdown/country columns by text via a LIKE search, so the example
     * values here are labels (e.g. "Male", "Ghana"), not raw IDs. Title
     * and Profession must be real, exact dropdown values - a made-up
     * value here would silently resolve to 0 on import, with no error.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return new Collection([
            [
                'title' => 'Mr.',
                'first_name' => 'John',
                'surname' => 'Doe',
                'other_names' => '',
                'date_of_birth' => '1990-01-01',
                'gender' => 'Male',
                'phone_number' => '0248000000',
                'whatsapp_number' => '0248000000',
                'marital_status' => 'Single',
                'nationality' => 'Ghana',
                'email' => 'john.doe@example.com',
                'address' => '123 Example Street',
                'position_held' => 'Member',
                'profession' => 'Ascension Minister',
                'residence_country' => 'Ghana',
                'languages_spoken' => 'English',
                'need_accommodation' => 1,
                'emergency_contacts_name' => 'Jane Doe',
                'emergency_contacts_relationship' => 'Sister',
                'emergency_contacts_phone_number' => '0248000001',
                'attendance_type' => 'In-Person',
                'disability' => 0,
                'special_needs' => 'None',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'Title',
            'First Name',
            'Surname',
            'Other Names',
            'Date of Birth',
            'Gender',
            'Phone Number',
            'WhatsApp Number',
            'Marital Status',
            'Nationality',
            'Email',
            'Address',
            'Position Held',
            'Profession',
            'Residence Country',
            'Languages Spoken',
            'Need Accommodation',
            "Emergency Contact's Name",
            "Emergency Contact's Relationship",
            "Emergency Contact's Phone Number",
            'Attendance Type',
            'Disability',
            'Special Needs',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Dropdown pickers for the short, enumerable fields and help-text
     * comments for the two free-text fields most likely to be mistyped -
     * so a coordinator filling this in by hand can't mistype Title,
     * Gender, Marital Status, Position Held, Profession, or Attendance
     * Type, and gets a hint for Nationality/Residence Country and the
     * two 1/0 fields.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                foreach (self::DROPDOWN_COLUMNS as ['column' => $column, 'lookup_code_id' => $lookupCodeId]) {
                    $options = Dropdown::where('lookup_code_id', $lookupCodeId)
                        ->where('active_flag', 1)
                        ->orderBy('full_name')
                        ->pluck('full_name')
                        ->all();

                    $this->applyListValidation($sheet, $column, $options);
                }

                $this->applyListValidation($sheet, self::ATTENDANCE_TYPE_COLUMN, ['In-Person', 'Online']);

                foreach (self::BOOLEAN_COLUMNS as $column) {
                    $this->applyListValidation($sheet, $column, ['1', '0']);
                }

                $sheet->getComment(self::NATIONALITY_COLUMN.'1')
                    ->getText()->createTextRun('Enter the full country name, e.g. Ghana');
                $sheet->getComment(self::RESIDENCE_COUNTRY_COLUMN.'1')
                    ->getText()->createTextRun('Enter the full country name, e.g. Ghana');
                foreach (self::BOOLEAN_COLUMNS as $column) {
                    $sheet->getComment($column.'1')
                        ->getText()->createTextRun('Enter 1 for Yes, 0 for No');
                }
            },
        ];
    }

    private function applyListValidation(Worksheet $sheet, string $column, array $options): void
    {
        $validation = new DataValidation;
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowDropDown(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('Invalid value');
        $validation->setError('Please pick a value from the dropdown list.');
        $validation->setFormula1('"'.implode(',', $options).'"');

        for ($row = 2; $row <= self::LAST_EXAMPLE_ROW; $row++) {
            $sheet->setDataValidation("{$column}{$row}", clone $validation);
        }
    }
}
