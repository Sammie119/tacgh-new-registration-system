<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private RoleService $role;

    public function __construct(RoleService $role)
    {
        $this->role = $role;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->role->index();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required'],
        ]);

        return $this->role->store($request->all());

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => ['required'],
        ]);

        return $this->role->update($request->all());
    }

    public function assignPermission(Request $request)
    {
        $request->validate([
            'permissions' => 'required'
        ]);

        return $this->role->assignPermission($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    static public function destroy($id)
    {
        return RoleService::destroy($id);
    }
}
