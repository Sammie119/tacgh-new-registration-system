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
        'gender' => ['column' => 'E', 'lookup_code_id' => 2],
        'marital_status' => ['column' => 'G', 'lookup_code_id' => 3],
        'position_held' => ['column' => 'I', 'lookup_code_id' => 5],
        'profession' => ['column' => 'J', 'lookup_code_id' => 10],
    ];

    private const DATE_OF_BIRTH_COLUMN = 'D';

    private const BOOLEAN_COLUMNS = ['M', 'N']; // Need Accommodation, Disability

    private const NATIONALITY_COLUMN = 'H';

    private const RESIDENCE_COUNTRY_COLUMN = 'K';

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
                'date_of_birth' => '01/01/1990',
                'gender' => 'Male',
                'phone_number' => '0248000000',
                'marital_status' => 'Single',
                'nationality' => 'Ghana',
                'position_held' => 'Member',
                'profession' => 'Ascension Minister',
                'residence_country' => 'Ghana',
                'languages_spoken' => 'English',
                'need_accommodation' => 'Yes',
                'disability' => 'No',
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
            'Date of Birth',
            'Gender',
            'Phone Number',
            'Marital Status',
            'Nationality',
            'Position Held',
            'Profession',
            'Residence Country',
            'Languages Spoken',
            'Need Accommodation',
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
     * comments for the free-text fields most likely to be mistyped -
     * so a coordinator filling this in by hand can't mistype Title,
     * Gender, Marital Status, Position Held, or Profession, and gets a
     * hint for Date of Birth, Nationality/Residence Country, and the
     * two Yes/No fields.
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

                foreach (self::BOOLEAN_COLUMNS as $column) {
                    $this->applyListValidation($sheet, $column, ['Yes', 'No']);
                }

                $sheet->getComment(self::DATE_OF_BIRTH_COLUMN.'1')
                    ->getText()->createTextRun('Enter date as dd/mm/yyyy, e.g. 01/01/1990');
                $sheet->getComment(self::NATIONALITY_COLUMN.'1')
                    ->getText()->createTextRun('Enter the full country name, e.g. Ghana');
                $sheet->getComment(self::RESIDENCE_COUNTRY_COLUMN.'1')
                    ->getText()->createTextRun('Enter the full country name, e.g. Ghana');
                foreach (self::BOOLEAN_COLUMNS as $column) {
                    $sheet->getComment($column.'1')
                        ->getText()->createTextRun('Enter Yes or No');
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
