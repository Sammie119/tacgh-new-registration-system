<?php

namespace App\Services\Admin;

use App\Models\Admin\Promotion;
use Carbon\Carbon;

class PromotionService
{
    public function store(array $data)
    {
        foreach ($data['promotions'] as $value) {
            if (empty($value['name'])) {
                // The always-present blank "add new" row - skip it if the
                // admin didn't actually fill anything in.
                continue;
            }

            $startsAt = Carbon::parse($value['starts_at']);
            $endsAt = Carbon::parse($value['ends_at']);

            if ($endsAt->lte($startsAt)) {
                return redirect(route('events', absolute: false))->with('error', 'Promotion end date must be after its start date.');
            }

            $id = $value['id'] ?? null;

            if ($this->overlapsExisting($data['event_id'], $startsAt, $endsAt, $id)) {
                return redirect(route('events', absolute: false))->with('error', "Promotion \"{$value['name']}\" overlaps another promotion already scheduled for this event.");
            }

            $attributes = [
                'event_id' => $data['event_id'],
                'name' => trim($value['name']),
                'applies_to' => $value['applies_to'],
                'discount_percentage' => $value['discount_percentage'],
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'active_flag' => isset($value['active_flag']) ? 1 : 0,
                'updated_by' => get_logged_in_user_id(),
            ];

            if (empty($id)) {
                Promotion::create($attributes + ['created_by' => get_logged_in_user_id()]);
            } else {
                $promotion = Promotion::find($id);
                if (! $promotion) {
                    continue;
                }

                $promotion->update($attributes);
            }
        }

        return redirect(route('events', absolute: false))->with('success', 'Promotions saved successfully!!!');
    }

    /**
     * Promotions for the same event may not have overlapping date ranges -
     * this keeps at most one ever active at a time, so a single
     * promotion_id on Registrant is enough to know which promotion (if
     * any) produced the current fee snapshot.
     */
    private function overlapsExisting(int $eventId, Carbon $startsAt, Carbon $endsAt, ?int $excludeId): bool
    {
        return Promotion::where('event_id', $eventId)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();
    }

    public static function destroy($id)
    {
        $record = Promotion::find($id);
        if ($record) {
            $record->delete();

            return 1;
        }

        return 0;
    }
}
