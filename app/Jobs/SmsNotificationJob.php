<?php

namespace App\Jobs;

use App\Http\Traits\SMSNotify;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SmsNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use SMSNotify;

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
        $this->sendSms($this->to, $this->msg);
    }
}
