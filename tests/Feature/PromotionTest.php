<?php

namespace Tests\Feature;

use App\Helpers\Utils;
use App\Models\Admin\Event;
use App\Models\Admin\EventFees;
use App\Models\Admin\OnlinePayment;
use App\Models\Admin\Promotion;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use App\Pipelines\Registration\RegistrantPipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionTest extends TestCase
{
    use RefreshDatabase;

    private function createEvent(string $prefix = 'TC'): Event
    {
        return Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => $prefix,
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
    }

    private function createFees(Event $event): array
    {
        $accommodation = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'accommodation', 'description' => 'Standard',
            'fee_amount' => 50, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $registration = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'registration_fee', 'description' => 'Standard',
            'fee_amount' => 100, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        return [$accommodation, $registration];
    }

    private function createPromotion(Event $event, array $overrides = []): Promotion
    {
        return Promotion::create(array_merge([
            'event_id' => $event->id,
            'name' => 'Early Bird',
            'applies_to' => 'both',
            'discount_percentage' => 20,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'active_flag' => 1,
            'created_by' => 1,
            'updated_by' => 1,
        ], $overrides));
    }

    private function createStage(Event $event, string $token): RegistrantStage
    {
        return RegistrantStage::create([
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'email' => 'ama@example.com', 'address' => 'Address',
            'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact', 'attendance_type' => 'In-Person',
            'event_id' => $event->id, 'disability' => 0, 'confirmed' => 'Yes', 'token' => $token,
        ]);
    }

    private function confirm(RegistrantStage $stage, Event $event, EventFees $accommodation, EventFees $registration): Registrant
    {
        $data = (new RegistrantPipe)->handle([
            'id' => $stage->id,
            'event_id' => $event->id,
            'accommodation_fee' => $accommodation->id,
            'registration_fee' => $registration->id,
            'amount_to_pay' => 150,
        ], fn ($data) => $data);

        return Registrant::find($data['id']);
    }

    // --- Discount math ---

    public function test_fee_is_discounted_within_an_active_promotion_window(): void
    {
        $event = $this->createEvent();
        [$accommodation] = $this->createFees($event);
        $this->createPromotion($event, ['applies_to' => 'accommodation', 'discount_percentage' => 20]);

        $this->assertSame(40.0, Utils::eventRegistrationFee($accommodation->id));
    }

    public function test_fee_is_unchanged_outside_the_promotion_window(): void
    {
        $event = $this->createEvent();
        [$accommodation] = $this->createFees($event);
        $this->createPromotion($event, [
            'applies_to' => 'accommodation',
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->subDays(2),
        ]);

        $this->assertSame(50.0, Utils::eventRegistrationFee($accommodation->id));
    }

    public function test_fee_is_unchanged_when_applies_to_does_not_match_the_fee_type(): void
    {
        $event = $this->createEvent();
        [$accommodation] = $this->createFees($event);
        $this->createPromotion($event, ['applies_to' => 'registration_fee']);

        $this->assertSame(50.0, Utils::eventRegistrationFee($accommodation->id));
    }

    public function test_applies_to_both_discounts_either_fee_type(): void
    {
        $event = $this->createEvent();
        [$accommodation, $registration] = $this->createFees($event);
        $this->createPromotion($event, ['applies_to' => 'both', 'discount_percentage' => 10]);

        $this->assertSame(45.0, Utils::eventRegistrationFee($accommodation->id));
        $this->assertSame(90.0, Utils::eventRegistrationFee($registration->id));
    }

    // --- Confirmation flow ---

    public function test_confirming_during_an_active_promotion_snapshots_the_discount_and_tags_promotion_id(): void
    {
        $event = $this->createEvent();
        [$accommodation, $registration] = $this->createFees($event);
        $promotion = $this->createPromotion($event, ['applies_to' => 'both', 'discount_percentage' => 20]);
        $stage = $this->createStage($event, 'TOK1');

        $registrant = $this->confirm($stage, $event, $accommodation, $registration);

        $this->assertSame(40.0, (float) $registrant->accommodation_fee);
        $this->assertSame(80.0, (float) $registrant->registration_fee);
        $this->assertSame(120.0, (float) $registrant->total_fee);
        $this->assertSame($promotion->id, $registrant->promotion_id);
    }

    public function test_confirming_outside_a_promotion_window_does_not_tag_promotion_id(): void
    {
        $event = $this->createEvent();
        [$accommodation, $registration] = $this->createFees($event);
        $stage = $this->createStage($event, 'TOK1');

        $registrant = $this->confirm($stage, $event, $accommodation, $registration);

        $this->assertSame(50.0, (float) $registrant->accommodation_fee);
        $this->assertSame(100.0, (float) $registrant->registration_fee);
        $this->assertNull($registrant->promotion_id);
    }

    // --- Revert behavior ---

    public function test_expired_unpaid_discount_reverts_to_original_fee(): void
    {
        $event = $this->createEvent();
        [$accommodation, $registration] = $this->createFees($event);
        $promotion = $this->createPromotion($event, [
            'applies_to' => 'both',
            'discount_percentage' => 20,
            'starts_at' => now()->subDays(3),
            'ends_at' => now()->subDay(),
        ]);

        $registrant = Registrant::create([
            'stage_id' => 1, 'event_id' => $event->id, 'registration_no' => 'TC-1',
            'accommodation_type' => $accommodation->id, 'accommodation_fee' => 40,
            'registration_type' => $registration->id, 'registration_fee' => 80,
            'total_fee' => 120, 'promotion_id' => $promotion->id,
        ]);

        OnlinePayment::create([
            'reg_id' => 1, 'event_id' => $event->id, 'amount_to_pay' => 120,
            'amount_paid' => 50, 'event_total_fee' => 120,
        ]);

        Promotion::revertExpiredDiscountIfUnpaid($registrant);
        $registrant->refresh();

        $this->assertSame(50.0, (float) $registrant->accommodation_fee);
        $this->assertSame(100.0, (float) $registrant->registration_fee);
        $this->assertSame(150.0, (float) $registrant->total_fee);
        $this->assertNull($registrant->promotion_id);
        $this->assertSame(150.0, (float) OnlinePayment::where('reg_id', 1)->value('event_total_fee'));
    }

    public function test_fully_paid_before_expiry_keeps_the_discount_permanently(): void
    {
        $event = $this->createEvent();
        [$accommodation, $registration] = $this->createFees($event);
        $promotion = $this->createPromotion($event, [
            'applies_to' => 'both',
            'discount_percentage' => 20,
            'starts_at' => now()->subDays(3),
            'ends_at' => now()->subDay(),
        ]);

        $registrant = Registrant::create([
            'stage_id' => 1, 'event_id' => $event->id, 'registration_no' => 'TC-1',
            'accommodation_type' => $accommodation->id, 'accommodation_fee' => 40,
            'registration_type' => $registration->id, 'registration_fee' => 80,
            'total_fee' => 120, 'promotion_id' => $promotion->id,
        ]);

        OnlinePayment::create([
            'reg_id' => 1, 'event_id' => $event->id, 'amount_to_pay' => 120,
            'amount_paid' => 120, 'event_total_fee' => 120,
        ]);

        Promotion::revertExpiredDiscountIfUnpaid($registrant);
        $registrant->refresh();

        $this->assertSame(40.0, (float) $registrant->accommodation_fee);
        $this->assertSame(120.0, (float) $registrant->total_fee);
        $this->assertSame($promotion->id, $registrant->promotion_id);
    }

    public function test_still_within_window_is_not_reverted_even_if_unpaid(): void
    {
        $event = $this->createEvent();
        [$accommodation, $registration] = $this->createFees($event);
        $promotion = $this->createPromotion($event, ['applies_to' => 'both', 'discount_percentage' => 20]);

        $registrant = Registrant::create([
            'stage_id' => 1, 'event_id' => $event->id, 'registration_no' => 'TC-1',
            'accommodation_type' => $accommodation->id, 'accommodation_fee' => 40,
            'registration_type' => $registration->id, 'registration_fee' => 80,
            'total_fee' => 120, 'promotion_id' => $promotion->id,
        ]);

        Promotion::revertExpiredDiscountIfUnpaid($registrant);
        $registrant->refresh();

        $this->assertSame(40.0, (float) $registrant->accommodation_fee);
        $this->assertSame($promotion->id, $registrant->promotion_id);
    }
}
