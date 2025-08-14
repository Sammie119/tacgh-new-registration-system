<?php

namespace App\Services\Admin;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function index()
    {
        $data['roles'] = Role::orderBy('name')->get();
        return view('admin.role.index', $data);
    }

    public function store(array $data)
    {
        $results = Role::firstOrCreate([
            'name' => trim($data['name'])
        ]);

        if($results){
            return redirect(route('roles', absolute: false))->with('success', 'Role Created Successfully!!!');
        }

        return redirect(route('roles', absolute: false))->with('error', 'Role Creation Unsuccessful!!!');
    }
    public function update(array $data)
    {
        $record = Role::find($data['id']);
        $results = $record->update(
            [
                'name' => trim($data['name'])
            ]
        );

        if($results){
            return redirect(route('roles', absolute: false))->with('success', 'Role Updated Successfully!!!');
        }

        return redirect(route('roles', absolute: false))->with('error', 'Role Update Unsuccessful!!!');
    }
    public function assignPermission(array $data)
    {
        $role = Role::find($data['id']);
        $role->syncPermissions($data['permissions']);

        return redirect(route('roles', absolute: false))->with('success', 'Permissions Assigned Added Successfully!!!');
    }
    static public function destroy($id)
    {
        $record = Role::find($id);
        if($record){
            $record->delete();
            return 1;
        }
        return 0;
    }
}
