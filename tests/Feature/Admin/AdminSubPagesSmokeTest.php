<?php

namespace Tests\Feature\Admin;

use App\Enums\RolesEnum;
use App\Models\Admin\Accommodation;
use App\Models\Admin\AccommodationBlock;
use App\Models\Admin\AccommodationRoom;
use App\Models\Admin\Download;
use App\Models\Admin\DropdownCategory;
use App\Models\Admin\Event;
use App\Models\Admin\EventVenue;
use App\Models\Admin\Form;
use App\Models\Admin\OnlinePayment;
use App\Models\FinancialEpisode;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Sweeps the "sub-pages" reached by clicking into a specific record from an
 * admin sidebar page: the execute_form create/edit/view modal endpoints, and
 * the few direct-parameter GET routes (accommodations/{id}, room/{id}, etc).
 * One test per page/type, asserting each loads instead of crashing.
 */
class AdminSubPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Event $event;

    private EventVenue $venue;

    private Accommodation $accommodation;

    private AccommodationBlock $block;

    private AccommodationRoom $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->venue = EventVenue::create([
            'name' => 'Main Campus', 'region_id' => 1, 'location' => 'Accra',
            'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $this->event = Event::create([
            'name' => 'Sub-Page Sweep Conference', 'description' => 'desc', 'code_prefix' => 'SPS',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'venue_id' => $this->venue->id, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $this->accommodation = Accommodation::create([
            'name' => 'Hostel A', 'venue_id' => $this->venue->id, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $this->block = AccommodationBlock::create([
            'name' => 'Block 1', 'residence_id' => $this->accommodation->id, 'total_rooms' => 10,
            'total_floors' => 1, 'gender' => 'M', 'created_by' => 1, 'updated_by' => 1,
        ]);

        $this->room = AccommodationRoom::create([
            'room_no' => 101, 'floor_no' => 1, 'floor_name' => 'Ground', 'block_id' => $this->block->id,
            'residence_id' => $this->accommodation->id, 'total_occupants' => 2, 'prefix' => 'R', 'suffix' => '',
            'created_by' => 1, 'updated_by' => 1,
        ]);

        $this->admin = User::factory()->create(['event_id' => $this->event->id]);

        foreach ([
            RolesEnum::SUPERADMIN, RolesEnum::SYSTEMADMIN, RolesEnum::SYSTEMDEVELOPER,
            RolesEnum::FINANCE, RolesEnum::ROOMALLOCATOR,
        ] as $roleEnum) {
            $this->admin->assignRole(Role::create(['name' => $roleEnum->value]));
        }
    }

    private function assertLoads(string $url): void
    {
        $response = $this->actingAs($this->admin)->get($url);

        $response->assertOk();
    }

    // ---- Direct-parameter routes ----

    public function test_venue_accommodations_list(): void
    {
        $this->assertLoads(route('accommodations', $this->venue->id));
    }

    public function test_room_show(): void
    {
        $this->assertLoads(route('room', $this->room->id));
    }

    public function test_form_report(): void
    {
        $form = Form::create(['user_id' => $this->admin->id, 'title' => 'Test Form']);

        $this->assertLoads(route('forms.report', $form));
    }

    public function test_print_financial_report(): void
    {
        $this->assertLoads(route('print_financial_report', 'generate_report'));
    }

    public function test_check_in_toggle(): void
    {
        $stage = RegistrantStage::create([
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'email' => 'checkin-sweep@example.com', 'address' => 'Address',
            'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact', 'attendance_type' => 'In-Person',
            'event_id' => $this->event->id, 'disability' => 0, 'confirmed' => 'Yes', 'token' => 'CHKTOK1',
        ]);
        Registrant::create(['registration_no' => 'CHK-1', 'stage_id' => $stage->id, 'event_id' => $this->event->id]);

        $response = $this->actingAs($this->admin)->get(route('check_in', $stage->id));

        $response->assertRedirect();
    }

    public function test_check_out_toggle(): void
    {
        $stage = RegistrantStage::create([
            'title' => 1, 'first_name' => 'Kofi', 'surname' => 'Boateng', 'gender' => 3,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234568', 'email' => 'checkout-sweep@example.com', 'address' => 'Address',
            'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact', 'attendance_type' => 'In-Person',
            'event_id' => $this->event->id, 'disability' => 0, 'confirmed' => 'Yes', 'token' => 'CHKTOK2',
        ]);
        Registrant::create(['registration_no' => 'CHK-2', 'stage_id' => $stage->id, 'event_id' => $this->event->id]);

        $response = $this->actingAs($this->admin)->get(route('check_out', $stage->id));

        $response->assertRedirect();
    }

    // ---- execute_form/view/{type}/{id} ----

    public function test_view_user_roles(): void
    {
        $this->assertLoads("/execute_form/view/user_roles/{$this->admin->id}");
    }

    public function test_view_assign_permissions(): void
    {
        $role = Role::create(['name' => 'Sweep Role']);

        $this->assertLoads("/execute_form/view/assign_permissions/{$role->id}");
    }

    public function test_view_dropdown(): void
    {
        $category = DropdownCategory::create([
            'lookup_short_code' => 'SW', 'look_up_name' => 'Sweep Category',
            'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $this->assertLoads("/execute_form/view/dropdown/{$category->id}");
    }

    public function test_view_accommodations(): void
    {
        $this->assertLoads("/execute_form/view/accommodations/{$this->venue->id}");
    }

    public function test_view_blocks_setup(): void
    {
        $this->assertLoads("/execute_form/view/blocks_setup/{$this->accommodation->id}");
    }

    public function test_view_generate_rooms(): void
    {
        $this->assertLoads("/execute_form/view/generate_rooms/{$this->block->id}");
    }

    public function test_view_fees(): void
    {
        $this->assertLoads("/execute_form/view/fees/{$this->event->id}");
    }

    public function test_view_financial_clearance(): void
    {
        $payment = OnlinePayment::create([
            'reg_id' => 1, 'event_id' => $this->event->id, 'amount_to_pay' => 100,
            'amount_paid' => 100, 'approved' => 0,
        ]);

        $this->assertLoads("/execute_form/view/financial_clearance/{$payment->id}");
    }

    // ---- execute_form/edit/{type}/{id} ----

    public function test_edit_user(): void
    {
        $this->assertLoads("/execute_form/edit/user/{$this->admin->id}");
    }

    public function test_edit_permission(): void
    {
        $permission = Permission::create(['name' => 'sweep-permission']);

        $this->assertLoads("/execute_form/edit/permission/{$permission->id}");
    }

    public function test_edit_role(): void
    {
        $role = Role::create(['name' => 'Sweep Edit Role']);

        $this->assertLoads("/execute_form/edit/role/{$role->id}");
    }

    public function test_edit_dropdown_category(): void
    {
        $category = DropdownCategory::create([
            'lookup_short_code' => 'SE', 'look_up_name' => 'Sweep Edit Category',
            'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $this->assertLoads("/execute_form/edit/dropdown_category/{$category->id}");
    }

    public function test_edit_venue(): void
    {
        $this->assertLoads("/execute_form/edit/venue/{$this->venue->id}");
    }

    public function test_edit_event(): void
    {
        $this->assertLoads("/execute_form/edit/event/{$this->event->id}");
    }

    public function test_edit_resident(): void
    {
        $this->assertLoads("/execute_form/edit/resident/{$this->accommodation->id}");
    }

    public function test_edit_forms(): void
    {
        $form = Form::create(['user_id' => $this->admin->id, 'title' => 'Sweep Edit Form']);

        $this->assertLoads("/execute_form/edit/forms/{$form->id}");
    }

    public function test_edit_financial_entry(): void
    {
        $entry = FinancialEpisode::create([
            'transaction_id' => 'SWEEP1', 'event_id' => $this->event->id, 'entry_type' => 'Income',
            'transaction_type' => 1, 'transaction_date' => now()->toDateString(),
            'amount' => 100, 'description' => 'Sweep test', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);

        $this->assertLoads("/execute_form/edit/financial_entry/{$entry->id}");
    }

    public function test_edit_downloads(): void
    {
        $download = Download::create([
            'event_id' => $this->event->id, 'file_name' => 'Sweep File', 'file_path' => 'downloads/sweep.pdf',
            'download_count' => 0, 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1,
        ]);

        $this->assertLoads("/execute_form/edit/downloads/{$download->id}");
    }

    // ---- execute_form/create/{type} ----

    public function test_create_user(): void
    {
        $this->assertLoads('/execute_form/create/user');
    }

    public function test_create_permission(): void
    {
        $this->assertLoads('/execute_form/create/permission');
    }

    public function test_create_role(): void
    {
        $this->assertLoads('/execute_form/create/role');
    }

    public function test_create_dropdown_category(): void
    {
        $this->assertLoads('/execute_form/create/dropdown_category');
    }

    public function test_create_venue(): void
    {
        $this->assertLoads('/execute_form/create/venue');
    }

    public function test_create_event(): void
    {
        $this->assertLoads('/execute_form/create/event');
    }

    public function test_create_forms(): void
    {
        $this->assertLoads('/execute_form/create/forms');
    }

    public function test_create_financial_entry(): void
    {
        $this->assertLoads('/execute_form/create/financial_entry');
    }

    public function test_create_downloads(): void
    {
        $this->assertLoads('/execute_form/create/downloads');
    }

    public function test_create_online_payment_correction(): void
    {
        $this->assertLoads('/execute_form/create/online_payment_correction');
    }
}
