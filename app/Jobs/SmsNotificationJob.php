<?php

namespace App\Jobs;

use App\Http\Traits\SMSNotify;
use App\Models\NotificationLog;
use App\Models\RegistrantStage;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SmsNotificationJob
{
    use Dispatchable, Queueable, SerializesModels;
    use SMSNotify;

    private string $to;

    private string $msg;

    private ?int $registrantId;

    public function __construct($to, $msg, ?int $registrantId = null)
    {
        $this->to = $to;
        $this->msg = $msg;
        $this->registrantId = $registrantId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $result = $this->sendSms($this->to, $this->msg);

        NotificationLog::create([
            'channel' => 'sms',
            'recipient' => $this->to,
            'message' => $this->msg,
            'success' => ($result['code'] ?? null) === '2000',
            'response' => is_array($result) ? json_encode($result) : (string) $result,
            'registrant_id' => $this->registrantId,
            'event_id' => $this->registrantId ? RegistrantStage::find($this->registrantId)?->event_id : null,
        ]);
    }
}
