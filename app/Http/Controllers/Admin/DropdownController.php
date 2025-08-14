<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Dropdown;
use App\Services\Admin\DropdownService;
use Illuminate\Http\Request;

class DropdownController extends Controller
{
    private DropdownService $dropdownService;

    public function __construct(DropdownService $dropdownService)
    {
        $this->dropdownService = $dropdownService;
    }

    public function indexCategory()
    {
        return $this->dropdownService->indexCategory();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'lookup_short_code' => ['required'],
            'look_up_name' => ['required'],
        ]);

        return $this->dropdownService->storeCategory($request->all());
    }

    public function updateCategory(Request $request)
    {
        $request->validate([
            'lookup_short_code' => ['required'],
            'look_up_name' => ['required'],
        ]);

        return $this->dropdownService->updateCategory($request->all());
    }

    static public function destroyCategory($id)
    {
        return DropdownService::deleteCategory($id);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
//        dd($request->all());
        $request->validate([
            'dropdown' => ['required'],
        ]);

        return $this->dropdownService->store($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    static public function destroy($id)
    {
        return DropdownService::delete($id);
    }
}
