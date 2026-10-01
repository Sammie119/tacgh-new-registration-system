<?php

namespace Tests\Feature\Registrant;

use App\Enums\RolesEnum;
use App\Models\Admin\Dropdown;
use App\Models\Admin\Event;
use App\Models\Admin\OnlinePayment;
use App\Models\Registrant;
use App\Models\RegistrantStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MultipleTitlesTest extends TestCase
{
    use RefreshDatabase;

    private Dropdown $mr;

    private Dropdown $dr;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mr = Dropdown::create(['lookup_code_id' => 22, 'full_name' => 'Mr.', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
        $this->dr = Dropdown::create(['lookup_code_id' => 22, 'full_name' => 'Dr.', 'active_flag' => 1, 'created_by' => 1, 'updated_by' => 1]);
    }

    private function createEvent(): Event
    {
        return Event::create([
            'name' => 'Test Conference', 'description' => 'A test event', 'code_prefix' => 'TC',
            'start_date' => now()->addDays(10)->toDateString(), 'end_date' => now()->addDays(12)->toDateString(),
            'is_payment_required' => 'No', 'status' => 'In-Progress', 'active_flag' => 1,
            'created_by' => 1, 'updated_by' => 1,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => [$this->mr->id, $this->dr->id],
            'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '0541234567', 'email' => 'ama@example.com',
            'profession' => 1, 'residence_country_id' => 1, 'languages_spoken' => 'English',
            'need_accommodation' => 1, 'emergency_contacts_name' => 'Kofi Mensah',
            'emergency_contacts_phone_number' => '0541234568', 'disability' => 0,
            'special_needs' => 'None', 'is_student' => 0,
        ], $overrides);
    }

    public function test_registering_with_several_titles_stores_them_comma_separated(): void
    {
        Bus::fake();
        $this->createEvent();

        $this->post(route('registrant.store'), $this->payload())
            ->assertRedirect(route('registrant_login'));

        $this->assertSame("{$this->mr->id},{$this->dr->id}", RegistrantStage::first()->title);
    }

    public function test_title_is_required(): void
    {
        $this->createEvent();

        $this->post(route('registrant.store'), $this->payload(['title' => []]))
            ->assertSessionHasErrors(['title' => 'Please select at least one title.']);
    }

    public function test_unknown_title_id_is_rejected(): void
    {
        $this->createEvent();

        $this->post(route('registrant.store'), $this->payload(['title' => [$this->mr->id, 9999]]))
            ->assertSessionHasErrors('title.1');
    }

    public function test_registration_form_renders_titles_as_checkboxes(): void
    {
        $html = $this->get(route('registrant.registration'))->assertOk()->getContent();

        foreach ([$this->mr, $this->dr] as $title) {
            $this->assertMatchesRegularExpression(
                '/<input[^>]*type="checkbox"[^>]*name="title\[\]"[^>]*value="'.$title->id.'"/s',
                $html
            );
        }
        $this->assertDoesNotMatchRegularExpression('/<select[^>]*name="title/', $html);
        // Rendered as a dropdown ("checkbox select") rather than loose boxes.
        $this->assertMatchesRegularExpression('/id="title_group".*?data-bs-toggle="dropdown".*?class="dropdown-menu[^"]*cbs-menu"/s', $html);
    }

    public function test_title_names_handles_multiple_and_legacy_single_values(): void
    {
        $this->assertSame('Mr. Dr.', title_names("{$this->mr->id},{$this->dr->id}"));
        $this->assertSame('Dr.', title_names((string) $this->dr->id));
        $this->assertSame('', title_names(null));
        $this->assertSame([$this->mr->id, $this->dr->id], title_ids(" {$this->mr->id}, {$this->dr->id} ,"));
    }

    public function test_admin_outstanding_balances_list_shows_all_titles(): void
    {
        $role = Role::create(['name' => RolesEnum::FINANCE->value]);
        $user = User::factory()->create(['event_id' => 1]);
        $user->assignRole($role);

        $stage = RegistrantStage::create([
            'title' => "{$this->mr->id},{$this->dr->id}", 'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'email' => 'ama@example.com', 'address' => 'N/A',
            'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Kofi', 'event_id' => 1, 'disability' => 0, 'token' => '123456',
        ]);
        Registrant::create(['registration_no' => 'TC-1', 'stage_id' => $stage->id, 'event_id' => 1]);
        OnlinePayment::create([
            'reg_id' => $stage->id, 'event_id' => 1, 'payment_mode' => 'Paystack', 'transaction_no' => 'TXN-1',
            'amount_to_pay' => 200, 'amount_paid' => 100, 'approved' => 0, 'payment_status' => 1,
        ]);

        $this->actingAs($user)->get(route('outstanding_balances'))
            ->assertOk()
            ->assertSee('MR. DR. AMA');
    }
}
