<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use App\Models\Admin\Form;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FormResponsesExport implements FromArray, WithHeadings
{
    protected $form;

    public function __construct(Form $form)
    {
        $this->form = $form->load('fields','responses.values');
    }

    public function array(): array
    {
        $rows = [];
        $fields = $this->form->fields;

        // header
        $headers = ['Submitted At', 'Submitter Name', 'Submitter Email'];
        foreach ($fields as $f) {
            $headers[] = $f->label;
        }
        $rows[] = $headers;

        foreach ($this->form->responses as $response) {
            $row = [
                $response->created_at->toDateTimeString(),
                $response->submitter_name,
                $response->submitter_email,
            ];
            foreach ($fields as $f) {
                $valObj = $response->values->firstWhere('field_id', $f->id);
                $val = $valObj->value ?? '';
                // convert stored pipe-separated checkbox values to commas
                if (strpos($val, '|') !== false) {
                    $val = str_replace('|', ', ', $val);
                }
                $row[] = $val;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    public function headings(): array
    {
        return []; // we already include headings in array()
    }
}
