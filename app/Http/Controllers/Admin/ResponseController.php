<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Form;
use App\Models\Admin\FormResponse;
use App\Models\Admin\FormResponseValue;
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    public function showForm($slug)
    {
        $form = Form::where('slug', $slug)->where('is_public', true)->with('fields')->firstOrFail();
        return view('registrant.show_form', compact('form'));
    }

    public function storeResponse(Request $request, $slug)
    {
        $form = Form::where('slug', $slug)->where('is_public', true)->with('fields')->firstOrFail();

        // Build validation rules dynamically
        $rules = [];
        foreach ($form->fields as $field) {
            $name = 'field_'.$field->id;
            if ($field->is_required) {
                $rules[$name] = 'required';
            } else {
                $rules[$name] = 'nullable';
            }

            // additional validation by type
            if (in_array($field->field_type, ['number'])) {
                $rules[$name] .= '|numeric';
            }
            if ($field->field_type === 'email') {
                $rules[$name] .= '|email';
            }
        }

        $validated = $request->validate($rules);

        $response = FormResponse::create([
            'form_id' => $form->id,
            'submitter_name' => $request->input('submitter_name'),
            'submitter_email' => $request->input('submitter_email'),
            'submitter_ip' => $request->ip(),
        ]);

        foreach ($form->fields as $field) {
            $fieldName = 'field_'.$field->id;
            $val = $request->input($fieldName);

            // For checkboxes, ensure we store array as comma separated
            if (is_array($val)) {
                $val = implode('|', $val);
            }

            FormResponseValue::create([
                'response_id' => $response->id,
                'field_id' => $field->id,
                'value' => $val,
            ]);
        }

        return redirect()->back()->with('success','Your response has been recorded. Thank you!');
    }
}
