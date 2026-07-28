<?php

namespace Tests\Feature;

use App\Jobs\SmsNotificationJob;
use App\Jobs\WhatsappNotificationJob;
use App\Models\NotificationLog;
use App\Models\RegistrantStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationLogTest extends TestCase
{
    use RefreshDatabase;

    // SMSNotify::sendSms()/sendWhatsApp() make raw curl_exec() calls, which
    // can't be intercepted with Http::fake(). Partial-mocking the job lets
    // us test the logging logic in handle() without a real network call.

    public function test_sms_job_records_a_successful_notification_log(): void
    {
        $job = \Mockery::mock(SmsNotificationJob::class, ['+233541234567', 'Hello', 42])->makePartial();
        $job->shouldReceive('sendSms')->once()->andReturn([
            'status' => 'success', 'code' => '2000', 'message' => 'messages sent successfully',
        ]);

        $job->handle();

        $this->assertDatabaseHas('notification_logs', [
            'channel' => 'sms',
            'recipient' => '+233541234567',
            'success' => 1,
            'registrant_id' => 42,
        ]);
    }

    public function test_sms_job_records_a_failed_notification_log(): void
    {
        $job = \Mockery::mock(SmsNotificationJob::class, ['+233541234567', 'Hello', null])->makePartial();
        $job->shouldReceive('sendSms')->once()->andReturn([
            'code' => -99, 'message' => 'Sorry some error occurred',
        ]);

        $job->handle();

        $this->assertDatabaseHas('notification_logs', [
            'channel' => 'sms',
            'recipient' => '+233541234567',
            'success' => 0,
        ]);
    }

    public function test_sms_job_records_the_registrants_event_id(): void
    {
        $stage = RegistrantStage::create([
            'title' => 1, 'first_name' => 'Ama', 'surname' => 'Mensah', 'gender' => 1,
            'date_of_birth' => '1990-01-01', 'marital_status' => 1, 'nationality_id' => 1,
            'phone_number' => '+233541234567', 'email' => 'ama@example.com', 'address' => 'Address',
            'position_held' => 1, 'profession' => 1, 'residence_country_id' => 1,
            'languages_spoken' => 'English', 'need_accommodation' => 1, 'emergency_contacts_name' => 'Contact',
            'attendance_type' => 'In-Person', 'event_id' => 7, 'disability' => 0, 'token' => 'TOK1',
        ]);

        $job = \Mockery::mock(SmsNotificationJob::class, [$stage->phone_number, 'Hello', $stage->id])->makePartial();
        $job->shouldReceive('sendSms')->once()->andReturn(['code' => '2000']);

        $job->handle();

        $this->assertDatabaseHas('notification_logs', [
            'registrant_id' => $stage->id,
            'event_id' => 7,
        ]);
    }

    public function test_whatsapp_job_records_a_successful_notification_log(): void
    {
        $job = \Mockery::mock(WhatsappNotificationJob::class, ['233541234567', 'Hello', 42])->makePartial();
        $job->shouldReceive('sendWhatsApp')->once()->andReturn(
            '{"data":{"status":"success"},"status":"success"}'
        );

        $job->handle();

        $this->assertDatabaseHas('notification_logs', [
            'channel' => 'whatsapp',
            'success' => 1,
            'registrant_id' => 42,
        ]);
    }

    public function test_whatsapp_job_records_an_application_level_failure(): void
    {
        // Real observed failure payload from waapi.app when the linked
        // WhatsApp session has disconnected.
        $job = \Mockery::mock(WhatsappNotificationJob::class, ['233541234567', 'Hello', null])->makePartial();
        $job->shouldReceive('sendWhatsApp')->once()->andReturn(
            '{"message":"Instance is not ready (status: qr). Please wait until the instance is in ready status before sending messages.","errors":[],"status":"error"}'
        );

        $job->handle();

        $this->assertDatabaseHas('notification_logs', [
            'channel' => 'whatsapp',
            'success' => 0,
        ]);
    }

    public function test_whatsapp_job_records_a_curl_failure(): void
    {
        $job = \Mockery::mock(WhatsappNotificationJob::class, ['233541234567', 'Hello', null])->makePartial();
        $job->shouldReceive('sendWhatsApp')->once()->andReturn('cURL Error #:Operation timed out');

        $job->handle();

        $log = NotificationLog::first();
        $this->assertFalse($log->success);
        $this->assertSame('cURL Error #:Operation timed out', $log->response);
    }
}
