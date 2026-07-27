<?php

namespace App\Services\Admin;

use App\Models\Admin\AssignPermissionToRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthService
{
    public function index()
    {
        $data['users'] = User::where('id', '!=', 1)->orderByDesc('created_at')->get();

        return view('auth.index', $data);
    }

    public function store(array $data)
    {
        $results = User::firstOrCreate([
            'email' => trim($data['email']),
        ],
            [
                'email' => trim($data['email']),
                'name' => trim($data['name']),
                'password' => Hash::make($data['password']),
                'active_flag' => isset($data['active_flag']) ? 1 : 0,
                'created_by' => get_logged_in_user_id(),
                'updated_by' => get_logged_in_user_id(),
            ]);

        if ($results) {
            return redirect(route('users', absolute: false))->with('success', 'User Created Successfully.');
        }

        return redirect(route('users', absolute: false))->with('error', 'User Creation Unsuccessful!!!');

    }

    public function update(array $data)
    {
        $record = User::find($data['id']);
        if (! $record) {
            return redirect(route('users', absolute: false))->with('error', 'User not found!!!');
        }

        $results = $record->update(
            [
                'name' => trim($data['name']),
                'email' => trim($data['email']),
                'active_flag' => isset($data['active_flag']) ? 1 : 0,
                'updated_by' => get_logged_in_user_id(),
            ]
        );

        if (! empty($data['password'])) {
            $results = $record->update(['password' => Hash::make($data['password'])]);
        }

        if ($results) {
            return redirect(route('users', absolute: false))->with('success', 'User Updated Successfully!!!');
        }

        return redirect(route('users', absolute: false))->with('error', 'User Update Unsuccessful!!!');
    }

    public static function delete($id)
    {
        $record = User::find($id);
        if ($record) {
            $record->delete();
            DB::table('assign_permission_to_roles')->where('user_id', $id)->delete();

            return 1;
        }

        return 0;
    }

    public function assignRolesToUser(array $data)
    {
        $user = User::find($data['id']);
        if (! $user) {
            return redirect(route('users', absolute: false))->with('error', 'User not found!!!');
        }

        $user->syncRoles($data['roles']);
        $user->syncPermissions($data['permissions']);

        DB::table('assign_permission_to_roles')->where('user_id', $data['id'])->delete();

        foreach ($data['roles'] as $key => $value) {
            $roleId = $this->getRoleNPermissionID($value, 'role');
            $permissionId = $this->getRoleNPermissionID($data['permissions'][$key] ?? null, 'permission');

            if (! $roleId || ! $permissionId) {
                continue;
            }

            AssignPermissionToRole::create([
                'user_id' => $data['id'],
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'created_by' => get_logged_in_user_id(),
                'updated_by' => get_logged_in_user_id(),
            ]);
        }

        return redirect(route('users', absolute: false))->with('success', 'Roles Assigned to User Successfully!!!');
    }

    private function getRoleNPermissionID($name, $type)
    {
        if ($name === null) {
            return null;
        }

        if ($type === 'role') {
            return Role::where('name', $name)->value('id');
        }

        return Permission::where('name', $name)->value('id');
    }
}
