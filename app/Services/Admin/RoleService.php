<?php

namespace App\Services\Admin;

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
            'name' => trim($data['name']),
        ]);

        if ($results) {
            activity('role-management')
                ->causedBy(auth()->user())
                ->performedOn($results)
                ->log("Role created: {$results->name}");

            return redirect(route('roles', absolute: false))->with('success', 'Role Created Successfully!!!');
        }

        return redirect(route('roles', absolute: false))->with('error', 'Role Creation Unsuccessful!!!');
    }

    public function update(array $data)
    {
        $record = Role::find($data['id']);
        if (! $record) {
            return redirect(route('roles', absolute: false))->with('error', 'Role not found!!!');
        }

        $oldName = $record->name;

        $results = $record->update(
            [
                'name' => trim($data['name']),
            ]
        );

        if ($results) {
            activity('role-management')
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['old_name' => $oldName, 'new_name' => $record->name])
                ->log('Role updated');

            return redirect(route('roles', absolute: false))->with('success', 'Role Updated Successfully!!!');
        }

        return redirect(route('roles', absolute: false))->with('error', 'Role Update Unsuccessful!!!');
    }

    public function assignPermission(array $data)
    {
        $role = Role::find($data['id']);
        if (! $role) {
            return redirect(route('roles', absolute: false))->with('error', 'Role not found!!!');
        }

        $role->syncPermissions($data['permissions']);

        activity('role-management')
            ->causedBy(auth()->user())
            ->performedOn($role)
            ->withProperties(['permissions' => $data['permissions']])
            ->log("Permissions assigned to role: {$role->name}");

        return redirect(route('roles', absolute: false))->with('success', 'Permissions Assigned Added Successfully!!!');
    }

    public static function destroy($id)
    {
        $record = Role::find($id);
        if ($record) {
            activity('role-management')
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->log("Role deleted: {$record->name}");

            $record->delete();

            return 1;
        }

        return 0;
    }
}
