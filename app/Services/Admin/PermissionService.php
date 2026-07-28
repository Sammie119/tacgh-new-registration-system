<?php

namespace App\Services\Admin;

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
            'name' => trim($data['name']),
        ]);

        if ($results) {
            activity('role-management')
                ->causedBy(auth()->user())
                ->performedOn($results)
                ->log("Permission created: {$results->name}");

            return redirect(route('permissions', absolute: false))->with('success', 'Permission Created Successfully!!!');
        }

        return redirect(route('permissions', absolute: false))->with('error', 'Permission Creation Unsuccessful!!!');
    }

    public function update(array $data)
    {
        $record = Permission::find($data['id']);
        if (! $record) {
            return redirect(route('permissions', absolute: false))->with('error', 'Permission not found!!!');
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
                ->log('Permission updated');

            return redirect(route('permissions', absolute: false))->with('success', 'Permission Updated Successfully!!!');
        }

        return redirect(route('permissions', absolute: false))->with('error', 'Permission Update Unsuccessful!!!');
    }

    public static function destroy($id)
    {
        $record = Permission::find($id);
        if ($record) {
            activity('role-management')
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->log("Permission deleted: {$record->name}");

            $record->delete();

            return 1;
        }

        return 0;
    }
}
