<?php

namespace App\Jobs;

use App\Http\Traits\SMSNotify;
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

    public function __construct($to, $msg)
    {
        $this->to = $to;
        $this->msg = $msg;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->sendWhatsApp($this->to, $this->msg);
    }
}
