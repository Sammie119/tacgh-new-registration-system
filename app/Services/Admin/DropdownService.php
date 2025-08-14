<?php

namespace App\Services\Admin;

use App\Models\Admin\Dropdown;
use App\Models\Admin\DropdownCategory;
use Illuminate\Support\Facades\DB;

class DropdownService
{
    public function indexCategory()
    {
        $data['categories'] = DropdownCategory::orderByDesc('created_at')->get();
        return view('admin.dropdown.index', $data);
    }

    public function storeCategory(array $data)
    {
        $results = DropdownCategory::firstOrCreate([
            'lookup_short_code' => trim($data['lookup_short_code']),
            'look_up_name' => trim($data['look_up_name']),
        ],
        [
            'active_flag' => isset($data['active_flag']) ? 1 : 0,
            'created_by' => get_logged_in_user_id(),
            'updated_by' => get_logged_in_user_id(),
        ]);


        if($results){
            return redirect(route('categories', absolute: false))->with('success', 'Lookup Code Created Successfully!!!');
        }

        return redirect(route('categories', absolute: false))->with('error', 'Lookup Code Creation Unsuccessful!!!');
    }

    public function updateCategory(array $data)
    {
        $record = DropdownCategory::find($data['id']);
        $results = $record->update(
            [
                'lookup_short_code' => trim($data['lookup_short_code']),
                'look_up_name' => trim($data['look_up_name']),
                'active_flag' => isset($data['active_flag']) ? 1 : 0,
                'updated_by' => get_logged_in_user_id(),
            ]
        );

        if($results){
            return redirect(route('categories', absolute: false))->with('success', 'Lookup Code Updated Successfully!!!');
        }

        return redirect(route('categories', absolute: false))->with('error', 'Lookup Code Update Unsuccessful!!!');
    }

    static public function deleteCategory($id)
    {
        $record = DropdownCategory::find($id);
        if($record){
            $record->delete();
            Dropdown::where('lookup_code_id', $id)->delete();
            return 1;
        }
        return 0;
    }

    public function store(array $data)
    {
//        dd($data);
        if(empty($data['dropdown'][1])){
            return redirect(route('categories', absolute: false))->with('error', 'List of Dropdown is Empty!!!');
        }

        $results = 0;
        foreach ($data['dropdown'] as $value) {
//            dd($value, isset($value['active_flag']));
            if(empty($value['id'])){
                $results = Dropdown::updateOrCreate([
                        'lookup_code_id' => $data['id'],
                        'full_name' => trim($value['full_name']),
                        'active_flag' => isset($value['active_flag']) ? 1 : 0,
                    ],
                    [
                        'created_by' => get_logged_in_user_id(),
                        'updated_by' =>  get_logged_in_user_id(),
                    ]);
            } else {
                $results = Dropdown::find($value['id'])->update([
                    'lookup_code_id' => $data['id'],
                    'full_name' => trim($value['full_name']),
                    'active_flag' => isset($value['active_flag']) ? 1 : 0,
                    'updated_by' =>  get_logged_in_user_id(),
                ]);
            }
        }

        if($results){
            return redirect(route('categories', absolute: false))->with('success', 'Lookup Created Successfully!!!');
        }

        return redirect(route('categories', absolute: false))->with('error', 'Lookup Creation Unsuccessful!!!');
    }

    static public function delete($id)
    {
        $record = Dropdown::find($id);
        if($record){
            $record->delete();
            return 1;
        }
        return 0;
    }
}
