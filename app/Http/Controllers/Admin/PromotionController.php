<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\PromotionService;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    private PromotionService $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required',
            'promotions' => 'required',
            'promotions.*.name' => 'required',
            'promotions.*.applies_to' => 'required',
            'promotions.*.discount_percentage' => 'required|numeric|min:0|max:100',
            'promotions.*.starts_at' => 'required|date',
            'promotions.*.ends_at' => 'required|date',
        ]);

        return $this->promotionService->store($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public static function destroy($id)
    {
        return PromotionService::destroy($id);
    }
}
