<?php

namespace App\Http\Controllers\Admin;

use App\Exports\FormResponsesExport;
use App\Http\Controllers\Controller;
use App\Models\Admin\Form;
use App\Models\Admin\FormField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class FormController extends Controller
{
    public function index()
    {
        $data['forms'] = Form::where('user_id', get_logged_in_user_id())->orderByDesc('id')->get();
        return view('admin.forms.index', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.field_type' => 'required|string',
        ]);

        $form = Form::create([
            'user_id' => get_logged_in_user_id(),
            'title' => $request->title,
            'description' => $request->description,
            'is_public' => $request->has('is_public'),
            'slug' => Str::slug($request->title).'-'.Str::random(6),
        ]);

        foreach ($request->fields as $index => $f) {
            FormField::create([
                'form_id' => $form->id,
                'label' => $f['label'],
                'field_type' => $f['field_type'],
                'options' => $f['options'] ? explode('|', trim($f['options'])) : null,
                'is_required' => !empty($f['is_required']),
                'order' => $index,
            ]);
        }

        return redirect()->route('forms')->with('success', 'Form Created Successfully!');
    }

    public function update(Request $request)
    {
//        dd($request->all());
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.field_type' => 'required|string',
        ]);

        $form = Form::find($request->id)->update([
            'user_id' => get_logged_in_user_id(),
            'title' => $request->title,
            'description' => $request->description,
            'is_public' => $request->has('is_public')
        ]);

        foreach ($request->fields as $index => $f) {
            $fields_array = ['radio', 'checkbox', 'dropdown'];
            if(isset($f['field_id'])) {
                FormField::find($f['field_id'])->update([
                    'form_id' => $request->id,
                    'label' => $f['label'],
                    'field_type' => $f['field_type'],
                    'options' => in_array($f['field_type'], $fields_array) ? explode('|', trim($f['options'])) : null,
                    'is_required' => !empty($f['is_required']),
                    'order' => $index,
                ]);
            } else {
                FormField::create([
                    'form_id' => $request->id,
                    'label' => $f['label'],
                    'field_type' => $f['field_type'],
                    'options' => $f['options'] ? explode('|', trim($f['options'])) : null,
                    'is_required' => !empty($f['is_required']),
                    'order' => $index,
                ]);
            }

        }

        return redirect()->route('forms')->with('success', 'Form Update Successfully!');
    }

    public function report(Form $form)
    {
        $form->load('fields','responses.values');
        return view('admin.forms.report', compact('form'));
    }

    public function export(Form $form)
    {
        return Excel::download(new FormResponsesExport($form), Str::slug($form->title).'-responses.xlsx');
    }
}
