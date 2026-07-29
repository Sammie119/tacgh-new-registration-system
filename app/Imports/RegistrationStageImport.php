<?php

namespace App\Imports;

use App\Helpers\Utils;
use App\Models\Admin\Country;
use App\Models\Admin\Dropdown;
use App\Models\RegistrantStage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class RegistrationStageImport extends DefaultValueBinder implements ToModel, WithCustomValueBinder, WithHeadingRow, WithValidation
{
    /**
     * Columns that must always be read as text. CSV has no cell-type
     * information, so PhpSpreadsheet's default numeric auto-detection
     * treats a leading "+" as a numeric sign and strips it (e.g.
     * "+233500000001" becomes "233500000001"), silently breaking the
     * phone_number regex validation below. XLSX preserves the "+" only if
     * the column happens to be formatted as text in the source file, so
     * this is enforced here instead of relying on that.
     */
    private const TEXT_COLUMNS = ['phone_number', 'whatsapp_number', 'emergency_contacts_phone_number'];

    /**
     * lookup_code_id values for each dropdown-backed field, matching the
     * Utils::getLookups() calls used to populate the registration form
     * (see RegistrantService). Without this, getLookup()'s fuzzy full_name
     * match can silently grab a row from an unrelated category (e.g. an
     * imported "Student" profession matching "Regular Student", a
     * Registration Type value, instead of the real Profession row).
     */
    private const LOOKUP_CODE_TITLE = 22;

    private const LOOKUP_CODE_GENDER = 2;

    private const LOOKUP_CODE_MARITAL_STATUS = 3;

    private const LOOKUP_CODE_POSITION_HELD = 5;

    private const LOOKUP_CODE_PROFESSION = 10;

    private $event_id;

    private $batch_no;

    /**
     * Column letter => slugified heading name, captured from row 1 as it's
     * read, so bindValue() can tell which column it's currently binding.
     */
    private array $headingColumns = [];

    public function __construct($event_id, $batch_no)
    {
        $this->event_id = $event_id;
        $this->batch_no = $batch_no;
    }

    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getRow() === 1) {
            $this->headingColumns[$cell->getColumn()] = Str::slug((string) $value, '_');

            return parent::bindValue($cell, $value);
        }

        $heading = $this->headingColumns[$cell->getColumn()] ?? null;

        if (in_array($heading, self::TEXT_COLUMNS, true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    /**
     * @param  array  $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private function dateConvertor($date): string
    {
        if (is_int($date)) {
            return date('Y-m-d', $date);
        }

        return date('Y-m-d', strtotime($date));
    }

    private function getLookup($name, int $lookupCodeId): int
    {
        $id = Dropdown::where('lookup_code_id', $lookupCodeId)->where('full_name', 'LIKE', '%'.$name.'%')->first();
        if ($id != null) {
            return $id->id;
        }

        return 0;
    }

    private function getCountry($name): int
    {
        $id = Country::where('name', 'LIKE', '%'.$name.'%')->first();
        if ($id != null) {
            return $id->id;
        }

        return 0;
    }

    public function model(array $row)
    {
        $token = Utils::generateUniqueToken(RegistrantStage::class, 6);

        return new RegistrantStage([
            'title' => $this->getLookup($row['title'], self::LOOKUP_CODE_TITLE),
            'first_name' => $row['first_name'],
            'surname' => $row['surname'],
            'other_names' => $row['other_names'],
            'marital_status' => $this->getLookup($row['marital_status'], self::LOOKUP_CODE_MARITAL_STATUS),
            'nationality_id' => $this->getCountry($row['nationality_id']),
            'whatsapp_number' => Utils::normalizeGhanaPhone($row['whatsapp_number']),
            'date_of_birth' => $this->dateConvertor($row['date_of_birth']),
            'gender' => $this->getLookup($row['gender'], self::LOOKUP_CODE_GENDER),
            'phone_number' => Utils::normalizeGhanaPhone($row['phone_number']),
            'event_id' => $this->event_id,
            'email' => $row['email'],
            'address' => $row['address'],
            'position_held' => $this->getLookup($row['position_held'], self::LOOKUP_CODE_POSITION_HELD),
            'profession' => $this->getLookup($row['profession'], self::LOOKUP_CODE_PROFESSION),
            'residence_country_id' => $this->getCountry($row['residence_country_id']),
            'languages_spoken' => $row['languages_spoken'],
            'need_accommodation' => $row['need_accommodation'],
            'emergency_contacts_name' => $row['emergency_contacts_name'],
            'emergency_contacts_relationship' => $row['emergency_contacts_relationship'],
            'emergency_contacts_phone_number' => Utils::normalizeGhanaPhone($row['emergency_contacts_phone_number']),
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
            'phone_number' => ['required', 'regex:'.Utils::GHANA_PHONE_REGEX],
            'whatsapp_number' => ['nullable', 'regex:'.Utils::GHANA_PHONE_REGEX],
            'email' => 'required|email',
            'address' => 'required',
            'position_held' => 'required',
            'profession' => 'required',
            'residence_country_id' => 'required',
            'languages_spoken' => 'required',
            'need_accommodation' => 'required|boolean',
            'emergency_contacts_name' => 'required',
            'emergency_contacts_relationship' => 'required',
            'emergency_contacts_phone_number' => ['required', 'regex:'.Utils::GHANA_PHONE_REGEX],
            'attendance_type' => 'required|in:In-Person,Online',
            //            'event_id' => 'required|exists:events,id',
            'disability' => 'required|boolean',
            'special_needs' => 'required',
        ];
    }
}
