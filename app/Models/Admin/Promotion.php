<?php

namespace App\Models\Admin;

use App\Models\Registrant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    /**
     * The promotion currently active for this event + fee type, if any.
     * At most one can match - promotions for the same event are not
     * allowed to have overlapping date ranges (enforced in
     * PromotionService::store()).
     */
    public static function activeFor(int $eventId, string $feeType): ?self
    {
        return static::where('event_id', $eventId)
            ->where('active_flag', 1)
            ->where(fn ($q) => $q->where('applies_to', 'both')->orWhere('applies_to', $feeType))
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->first();
    }

    /**
     * Whichever promotion is currently active for this event, across
     * either fee type - used to tag a fee snapshot with the promotion
     * that produced it.
     */
    public static function currentlyActiveFor(int $eventId): ?self
    {
        return static::activeFor($eventId, 'accommodation') ?? static::activeFor($eventId, 'registration_fee');
    }

    /**
     * If this registrant's fee snapshot was discounted by a promotion
     * that has since ended, and they still haven't paid in full, revert
     * their fees to the original (undiscounted) amounts and clear
     * promotion_id. Called lazily wherever a registrant's balance is
     * actually checked - there's no scheduled task in this app to sweep
     * everyone centrally (see RegistrantPipe/PaymentService callers).
     */
    public static function revertExpiredDiscountIfUnpaid(Registrant $registrant): void
    {
        if (! $registrant->promotion_id) {
            return;
        }

        $promotion = static::find($registrant->promotion_id);
        if ($promotion && $promotion->ends_at >= now()) {
            return; // still within the window
        }

        $paid = OnlinePayment::where('reg_id', $registrant->stage_id)->sum('amount_paid');
        if ($paid >= $registrant->total_fee) {
            return; // paid in full within the window - keeps the discount permanently
        }

        $originalAccommodation = $registrant->accommodation_type ? (float) (EventFees::find($registrant->accommodation_type)?->fee_amount ?? 0) : 0;
        $originalRegistration = $registrant->registration_type ? (float) (EventFees::find($registrant->registration_type)?->fee_amount ?? 0) : 0;
        $originalTotal = $originalAccommodation + $originalRegistration;

        $registrant->update([
            'accommodation_fee' => $originalAccommodation,
            'registration_fee' => $originalRegistration,
            'total_fee' => $originalTotal,
            'promotion_id' => null,
        ]);

        OnlinePayment::where('reg_id', $registrant->stage_id)->update([
            'amount_to_pay' => $originalTotal,
            'event_total_fee' => $originalTotal,
        ]);
    }
}
