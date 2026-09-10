<?php

namespace App\Imports;

use App\Helpers\Utils;
use App\Models\Admin\Country;
use App\Models\Admin\Dropdown;
use App\Models\RegistrantStage;
use DateTime;
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
    private const TEXT_COLUMNS = ['phone_number'];

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

    private $email;

    private $is_student;

    private $institution_name;

    /**
     * Column letter => slugified heading name, captured from row 1 as it's
     * read, so bindValue() can tell which column it's currently binding.
     */
    private array $headingColumns = [];

    /**
     * Is Student/Institution Name are collected once from the batch
     * coordinator (Batch Information card) rather than per registrant -
     * every row imported from this file gets the same values, same as
     * $email above.
     */
    public function __construct($event_id, $batch_no, $email, $is_student = false, $institution_name = null)
    {
        $this->event_id = $event_id;
        $this->batch_no = $batch_no;
        $this->email = $email;
        $this->is_student = $is_student;
        $this->institution_name = $institution_name;
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
     * Converts the template's Date of Birth entry to Y-m-d for storage.
     * Explicitly parses d/m/Y (the format the template now instructs) and
     * the old Y-m-d, rather than relying on strtotime(), which reads a
     * slash-separated date the "American" way (m/d/Y) and would silently
     * swap day and month for a value like 03/04/1990.
     */
    private function dateConvertor($date): string
    {
        if (is_int($date)) {
            return date('Y-m-d', $date);
        }

        $date = trim((string) $date);

        foreach (['d/m/Y', 'Y-m-d'] as $format) {
            $parsed = DateTime::createFromFormat($format, $date);
            if ($parsed !== false && $parsed->format($format) === $date) {
                return $parsed->format('Y-m-d');
            }
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

    /**
     * Casts the template's Yes/No picker (case-insensitive) back to the
     * 1/0 stored on need_accommodation/disability. Also accepts the old
     * 1/0 values so a batch started on a previously-downloaded template
     * still imports correctly.
     */
    private function toBoolean($value): int
    {
        return in_array(strtolower(trim((string) $value)), ['yes', '1'], true) ? 1 : 0;
    }

    /**
     * WhatsApp Number, Email, Address, Other Names, and the Emergency
     * Contact/Attendance Type columns are no longer collected on the
     * batch template - they're re-collected (and overwrite these) on the
     * batch confirmation screen. email/address/emergency_contacts_name
     * are NOT NULL with no DB default, so they need an explicit
     * placeholder; the rest are nullable or have their own DB default
     * and are simply omitted here. Email, Is Student and Institution Name
     * are collected once from the batch coordinator and stamped onto
     * every row (see the constructor).
     */
    public function model(array $row)
    {
        $token = Utils::generateUniqueToken(RegistrantStage::class, 6);

        return new RegistrantStage([
            'title' => $this->getLookup($row['title'], self::LOOKUP_CODE_TITLE),
            'first_name' => $row['first_name'],
            'surname' => $row['surname'],
            'marital_status' => $this->getLookup($row['marital_status'], self::LOOKUP_CODE_MARITAL_STATUS),
            'nationality_id' => $this->getCountry($row['nationality']),
            'whatsapp_number' => Utils::normalizeGhanaPhone($row['phone_number']),
            'date_of_birth' => $this->dateConvertor($row['date_of_birth']),
            'gender' => $this->getLookup($row['gender'], self::LOOKUP_CODE_GENDER),
            'phone_number' => Utils::normalizeGhanaPhone($row['phone_number']),
            'event_id' => $this->event_id,
            'email' => $this->email,
            'address' => 'N/A',
            'position_held' => $this->getLookup($row['position_held'], self::LOOKUP_CODE_POSITION_HELD),
            'profession' => $this->getLookup($row['profession'], self::LOOKUP_CODE_PROFESSION),
            'residence_country_id' => $this->getCountry($row['residence_country']),
            'languages_spoken' => $row['languages_spoken'],
            'need_accommodation' => $this->toBoolean($row['need_accommodation']),
            'emergency_contacts_name' => 'N/A',
            'disability' => $this->toBoolean($row['disability']),
            'special_needs' => $row['special_needs'],
            'is_student' => $this->is_student ? 1 : 0,
            'institution_name' => $this->is_student ? $this->institution_name : null,
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
            'gender' => 'required',
            'date_of_birth' => 'required|date_format:d/m/Y,Y-m-d',
            'marital_status' => 'required',
            'nationality' => 'required',
            'phone_number' => ['required', 'regex:'.Utils::GHANA_PHONE_REGEX],
            'position_held' => 'required',
            'profession' => 'required',
            'residence_country' => 'required',
            'languages_spoken' => 'required',
            'need_accommodation' => ['required', 'regex:/^(yes|no|1|0)$/i'],
            'disability' => ['required', 'regex:/^(yes|no|1|0)$/i'],
            'special_needs' => 'required',
        ];
    }
}
