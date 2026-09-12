<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\Accommodation;
use App\Models\Admin\AccommodationBlock;
use App\Models\Admin\AccommodationRoom;
use App\Models\Admin\Event;
use App\Models\Admin\EventFees;
use App\Models\Admin\EventVenue;
use App\Models\Admin\OnlinePayment;
use App\Models\BatchLog;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BatchRoomAllocationTest extends TestCase
{
    use RefreshDatabase;

    private function roomAllocatorUser(Event $event): User
    {
        $role = Role::create(['name' => RolesEnum::ROOMALLOCATOR->value]);
        $user = User::factory()->create(['event_id' => $event->id]);
        $user->assignRole($role);

        return $user;
    }

    private function createEvent(): Event
    {
        $venue = EventVenue::create([
            'name' => 'Main Campus', 'region_id' => 1, 'location' => 'Address',
            'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        return Event::create([
            'name' => 'Test Conference', 'description' => 'desc', 'code_prefix' => 'TC',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'venue_id' => $venue->id, 'created_by' => 1, 'updated_by' => 1,
        ]);
    }

    private function createRoom(Event $event, array $overrides = []): AccommodationRoom
    {
        $residence = Accommodation::create([
            'name' => 'Hostel A', 'venue_id' => $event->venue_id, 'gender' => 'A',
            'status' => 'Active', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $block = AccommodationBlock::create([
            'name' => 'Block 1', 'residence_id' => $residence->id, 'total_rooms' => 10,
            'total_floors' => 1, 'gender' => 'A', 'status' => 'Active', 'created_by' => 1, 'updated_by' => 1,
        ]);

        return AccommodationRoom::create(array_merge([
            'room_no' => 101, 'floor_no' => 1, 'floor_name' => 'Ground', 'block_id' => $block->id,
            'residence_id' => $residence->id, 'total_occupants' => 2, 'prefix' => 'R', 'suffix' => '',
            'gender' => 'A', 'type' => 'Regular', 'assign' => 1, 'created_by' => 1, 'updated_by' => 1,
        ], $overrides));
    }

    private function createBatchMember(Event $event, int $batchNo, string $token, array $stageOverrides = [], array $registrantOverrides = []): array
    {
        $stage = RegistrantStage::create(array_merge([
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 3,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'whatsapp_number' => '+233541234567', 'email' => 'ama@example.com', 'address' => 'Address',
            'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact', 'attendance_type' => 'In-Person',
            'event_id' => $event->id, 'disability' => 0, 'confirmed' => 'Yes',
            'token' => $token, 'batch_no' => $batchNo,
        ], $stageOverrides));

        $registrant = Registrant::create(array_merge([
            'registration_no' => 'REG-'.$token, 'stage_id' => $stage->id, 'event_id' => $event->id,
            'total_fee' => 100,
        ], $registrantOverrides));

        return [$stage, $registrant];
    }

    public function test_assigning_a_batch_only_rooms_the_eligible_registrants(): void
    {
        Bus::fake();
        $event = $this->createEvent();
        $user = $this->roomAllocatorUser($event);
        $this->createRoom($event, ['total_occupants' => 10]);

        $batchLog = BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK123', 'total_registration_fees' => 0,
        ]);
        $accommodationFee = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'accommodation', 'description' => 'Regular Room',
            'fee_amount' => 100, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $registrationFee = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'registration_fee', 'description' => 'Standard',
            'fee_amount' => 0, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        // Eligible: fully paid, needs accommodation, no room yet.
        // registration_type must reference a real event_fees row too -
        // vw_registration inner-joins on it, and event_registrant_age()
        // (used by autoRoomAllocation's age guard) reads from that view.
        [$paidStage, $paidRegistrant] = $this->createBatchMember($event, 1, 'TOK1', [], [
            'accommodation_type' => $accommodationFee->id, 'registration_type' => $registrationFee->id,
        ]);
        OnlinePayment::create(['reg_id' => $paidStage->id, 'event_id' => $event->id, 'amount_paid' => 100, 'amount_to_pay' => 100]);

        // Not eligible: hasn't paid.
        [$unpaidStage, $unpaidRegistrant] = $this->createBatchMember($event, 1, 'TOK2');

        // Not eligible: doesn't need accommodation.
        [$noAccomStage, $noAccomRegistrant] = $this->createBatchMember($event, 1, 'TOK3', ['need_accommodation' => 0]);
        OnlinePayment::create(['reg_id' => $noAccomStage->id, 'event_id' => $event->id, 'amount_paid' => 100, 'amount_to_pay' => 100]);

        // Not eligible: already has a room.
        [$alreadyRoomedStage, $alreadyRoomedRegistrant] = $this->createBatchMember($event, 1, 'TOK4', [], ['room_no' => 999]);
        OnlinePayment::create(['reg_id' => $alreadyRoomedStage->id, 'event_id' => $event->id, 'amount_paid' => 100, 'amount_to_pay' => 100]);

        $response = $this->actingAs($user)->post(route('batch_room_allocation.assign'), ['batch_no' => 1]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertNotNull($paidRegistrant->fresh()->room_no);
        $this->assertNull($unpaidRegistrant->fresh()->room_no);
        $this->assertNull($noAccomRegistrant->fresh()->room_no);
        $this->assertSame(999, $alreadyRoomedRegistrant->fresh()->room_no);
    }

    public function test_a_partially_paid_but_finance_approved_registrant_is_still_eligible(): void
    {
        // Same rule as AssignRoomEpisodeService::addRoomMate(): full
        // payment needs no approval, but a partial payment paired with
        // financial clearance (approved == 2) is eligible too.
        Bus::fake();
        $event = $this->createEvent();
        $user = $this->roomAllocatorUser($event);
        $this->createRoom($event, ['total_occupants' => 10]);

        BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK123', 'total_registration_fees' => 0,
        ]);
        // vw_registration inner-joins on registration_type/accommodation_type
        // (event_registrant_age(), used by autoRoomAllocation's age guard,
        // reads from that view), so both must reference real event_fees rows.
        $accommodationFee = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'accommodation', 'description' => 'Regular Room',
            'fee_amount' => 100, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $registrationFee = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'registration_fee', 'description' => 'Standard',
            'fee_amount' => 0, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $feeOverrides = ['accommodation_type' => $accommodationFee->id, 'registration_type' => $registrationFee->id];

        [$approvedStage, $approvedRegistrant] = $this->createBatchMember($event, 1, 'TOK1', [], $feeOverrides);
        OnlinePayment::create(['reg_id' => $approvedStage->id, 'event_id' => $event->id, 'amount_paid' => 40, 'amount_to_pay' => 100, 'approved' => 2]);

        // Same partial amount, but never approved - stays ineligible.
        [$unapprovedStage, $unapprovedRegistrant] = $this->createBatchMember($event, 1, 'TOK2', [], $feeOverrides);
        OnlinePayment::create(['reg_id' => $unapprovedStage->id, 'event_id' => $event->id, 'amount_paid' => 40, 'amount_to_pay' => 100, 'approved' => 1]);

        $response = $this->actingAs($user)->post(route('batch_room_allocation.assign'), ['batch_no' => 1]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertNotNull($approvedRegistrant->fresh()->room_no);
        $this->assertNull($unapprovedRegistrant->fresh()->room_no);
    }

    public function test_a_zero_fee_registrant_with_nothing_paid_needs_finance_approval_too(): void
    {
        // A 0.00 total_fee with 0.00 paid is NOT vacuously "fully paid" -
        // it needs the same explicit approval a genuine partial payment
        // would, same as everywhere else this rule applies.
        Bus::fake();
        $event = $this->createEvent();
        $user = $this->roomAllocatorUser($event);
        $this->createRoom($event, ['total_occupants' => 10]);

        BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK123', 'total_registration_fees' => 0,
        ]);
        $accommodationFee = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'accommodation', 'description' => 'Regular Room',
            'fee_amount' => 0, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $registrationFee = EventFees::create([
            'event_id' => $event->id, 'fee_type' => 'registration_fee', 'description' => 'Standard',
            'fee_amount' => 0, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);
        $feeOverrides = ['accommodation_type' => $accommodationFee->id, 'registration_type' => $registrationFee->id, 'total_fee' => 0];

        // No OnlinePayment row at all - not eligible.
        [, $noPaymentRowRegistrant] = $this->createBatchMember($event, 1, 'TOK1', [], $feeOverrides);

        // approved == 2 despite 0.00/0.00 - eligible.
        [$approvedStage, $approvedZeroFeeRegistrant] = $this->createBatchMember($event, 1, 'TOK2', [], $feeOverrides);
        OnlinePayment::create(['reg_id' => $approvedStage->id, 'event_id' => $event->id, 'amount_paid' => 0, 'amount_to_pay' => 0, 'approved' => 2]);

        $response = $this->actingAs($user)->post(route('batch_room_allocation.assign'), ['batch_no' => 1]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertNull($noPaymentRowRegistrant->fresh()->room_no);
        $this->assertNotNull($approvedZeroFeeRegistrant->fresh()->room_no);
    }

    public function test_a_registrant_with_no_available_room_is_reported_as_skipped(): void
    {
        Bus::fake();
        $event = $this->createEvent();
        $user = $this->roomAllocatorUser($event);
        // No rooms created at all for this event's venue.

        BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK123', 'total_registration_fees' => 0,
        ]);
        [$stage, $registrant] = $this->createBatchMember($event, 1, 'TOK1');
        OnlinePayment::create(['reg_id' => $stage->id, 'event_id' => $event->id, 'amount_paid' => 100, 'amount_to_pay' => 100]);

        $response = $this->actingAs($user)->post(route('batch_room_allocation.assign'), ['batch_no' => 1]);

        $response->assertSessionHas('success', function ($message) {
            return str_contains($message, '1 had no available room');
        });
        $this->assertNull($registrant->fresh()->room_no);
    }

    public function test_a_batch_no_from_a_different_event_is_rejected(): void
    {
        Bus::fake();
        $event = $this->createEvent();
        $otherEvent = $this->createEvent();
        $user = $this->roomAllocatorUser($event);
        $this->createRoom($otherEvent, ['total_occupants' => 10]);

        BatchLog::create([
            'batch_no' => 1, 'event_id' => $otherEvent->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK123', 'total_registration_fees' => 0,
        ]);
        [$stage, $registrant] = $this->createBatchMember($otherEvent, 1, 'TOK1');
        OnlinePayment::create(['reg_id' => $stage->id, 'event_id' => $otherEvent->id, 'amount_paid' => 100, 'amount_to_pay' => 100]);

        $response = $this->actingAs($user)->post(route('batch_room_allocation.assign'), ['batch_no' => 1]);

        $response->assertSessionHas('error');
        $this->assertNull($registrant->fresh()->room_no);
    }

    public function test_a_user_without_the_required_role_cannot_assign_batch_rooms(): void
    {
        $event = $this->createEvent();
        $role = Role::create(['name' => RolesEnum::FINANCE->value]);
        $user = User::factory()->create(['event_id' => $event->id]);
        $user->assignRole($role);

        BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK123', 'total_registration_fees' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('batch_room_allocation.assign'), ['batch_no' => 1]);

        $response->assertForbidden();
    }

    public function test_index_shows_needing_and_eligible_counts_per_batch(): void
    {
        $event = $this->createEvent();
        $user = $this->roomAllocatorUser($event);
        $this->createRoom($event, ['total_occupants' => 10]);

        BatchLog::create([
            'batch_no' => 1, 'event_id' => $event->id, 'email' => 'batch@example.com',
            'confirmed' => 'No', 'token' => 'BTOK123', 'total_registration_fees' => 0,
        ]);
        [$paidStage] = $this->createBatchMember($event, 1, 'TOK1');
        OnlinePayment::create(['reg_id' => $paidStage->id, 'event_id' => $event->id, 'amount_paid' => 100, 'amount_to_pay' => 100]);
        $this->createBatchMember($event, 1, 'TOK2');

        $response = $this->actingAs($user)->get(route('batch_room_allocation'));

        $response->assertOk();
        $response->assertSeeInOrder(['Batch No.', '1', 'batch@example.com']);
    }
}
