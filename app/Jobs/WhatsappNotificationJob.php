<?php

namespace App\Jobs;

use App\Http\Traits\SMSNotify;
use App\Models\NotificationLog;
use App\Models\RegistrantStage;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhatsappNotificationJob
{
    use Dispatchable, Queueable, SerializesModels;
    use SMSNotify;

    /**
     * Create a new job instance.
     */
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
        $response = $this->sendWhatsApp($this->to, $this->msg);
        $decoded = json_decode($response, true);
        $success = is_array($decoded) && ($decoded['status'] ?? null) === 'success';

        NotificationLog::create([
            'channel' => 'whatsapp',
            'recipient' => $this->to,
            'message' => $this->msg,
            'success' => $success,
            'response' => $response,
            'registrant_id' => $this->registrantId,
            'event_id' => $this->registrantId ? RegistrantStage::find($this->registrantId)?->event_id : null,
        ]);
    }
}
