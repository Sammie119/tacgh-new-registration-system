<?php

namespace Tests\Feature;

use App\Helpers\Utils;
use App\Models\BatchLog;
use App\Models\RegistrantStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UtilsGenerateUniqueTokenTest extends TestCase
{
    use RefreshDatabase;

    /**
     * generateToken(1) draws from a fixed 36-character alphabet
     * (0-9, A-Z), so occupying 35 of the 36 possible single-character
     * tokens makes a random collision on the 36th (free) draw a near
     * certainty within a modest number of attempts - this is what makes
     * the retry loop's behavior deterministically testable instead of
     * relying on luck.
     */
    private const ALPHABET = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

    public function test_it_retries_until_it_finds_the_one_free_token(): void
    {
        $chars = self::ALPHABET;
        $free = array_pop($chars);
        foreach ($chars as $i => $char) {
            RegistrantStage::create($this->stageAttributes(['token' => $char, 'phone_number' => "+23354120{$i}"]));
        }

        // Only one token (the last alphabet character) is actually free -
        // with a high attempt cap this must eventually land on it, proving
        // the loop keeps re-checking the DB rather than trusting one draw.
        $token = Utils::generateUniqueToken(RegistrantStage::class, 1, 500);

        $this->assertSame($free, $token);
    }

    public function test_it_throws_after_max_attempts_when_no_token_is_free(): void
    {
        foreach (self::ALPHABET as $i => $char) {
            RegistrantStage::create($this->stageAttributes(['token' => $char, 'phone_number' => "+23354130{$i}"]));
        }

        $this->expectException(\RuntimeException::class);

        Utils::generateUniqueToken(RegistrantStage::class, 1, 5);
    }

    public function test_it_treats_a_soft_deleted_rows_token_as_taken(): void
    {
        // The DB-level unique index doesn't exempt soft-deleted rows, so the
        // collision check must not either - otherwise generateUniqueToken()
        // could hand back a token that then fails to insert. Occupy every
        // token but one, and soft-delete that last one: if withTrashed()
        // were missing, that freed-looking slot would let this succeed;
        // with it, every token is considered taken and it must throw.
        foreach (self::ALPHABET as $i => $char) {
            $stage = RegistrantStage::create($this->stageAttributes(['token' => $char, 'phone_number' => "+23354140{$i}"]));
        }
        $stage->delete();

        $this->expectException(\RuntimeException::class);

        Utils::generateUniqueToken(RegistrantStage::class, 1, 500);
    }

    public function test_the_database_rejects_a_duplicate_token_as_a_final_backstop(): void
    {
        RegistrantStage::create($this->stageAttributes(['token' => 'DUPTOK']));

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        RegistrantStage::create($this->stageAttributes(['token' => 'DUPTOK', 'phone_number' => '+233541234599']));
    }

    public function test_it_works_for_batch_log_tokens_too(): void
    {
        BatchLog::create([
            'batch_no' => 1, 'event_id' => 1, 'email' => 'batch@example.com',
            'token' => 'BATCHTOK', 'total_registration_fees' => 0,
        ]);

        for ($i = 0; $i < 20; $i++) {
            $this->assertNotSame('BATCHTOK', Utils::generateUniqueToken(BatchLog::class));
        }
    }

    private function stageAttributes(array $overrides = []): array
    {
        return array_merge([
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'email' => 'ama@example.com', 'address' => 'Address',
            'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1,
            'emergency_contacts_name' => 'Contact', 'attendance_type' => 'In-Person',
            'event_id' => 1, 'disability' => 0, 'confirmed' => 'Yes',
        ], $overrides);
    }
}
