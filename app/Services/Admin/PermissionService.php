<?php

namespace App\Services\Admin;

use App\Models\Admin\Organisation;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    public function index()
    {
        $data['permissions'] = Permission::orderByDesc('created_at')->get();
        return view('admin.permission.index', $data);
    }

    public function store(array $data)
    {
        $results = Permission::firstOrCreate([
                'name' => trim($data['name'])
            ]);


        if($results){
            return redirect(route('permissions', absolute: false))->with('success', 'Permission Created Successfully!!!');
        }

        return redirect(route('permissions', absolute: false))->with('error', 'Permission Creation Unsuccessful!!!');
    }

    public function update(array $data)
    {
        $record = Permission::find($data['id']);
        $results = $record->update(
            [
                'name' => trim($data['name'])
            ]
        );

        if($results){
            return redirect(route('permissions', absolute: false))->with('success', 'Permission Updated Successfully!!!');
        }

        return redirect(route('permissions', absolute: false))->with('error', 'Permission Update Unsuccessful!!!');
    }

    static public function destroy($id)
    {
        $record = Permission::find($id);
        if($record){
            $record->delete();
            return 1;
        }
        return 0;
    }
}
