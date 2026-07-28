<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class QueueRunController extends Controller
{
    /**
     * Drain the notification queue. Meant to be pinged periodically by an
     * external webcron service, as a substitute for shell/cron access.
     */
    public function run(string $secret)
    {
        $configured = config('cron.secret');

        abort_if(empty($configured) || ! hash_equals($configured, $secret), 404);

        // Skip if another ping is already draining the queue, rather than
        // stacking up overlapping queue:work runs.
        $ran = Cache::lock('queue-run-lock', 60)->get(function () {
            Artisan::call('queue:work', [
                '--stop-when-empty' => true,
                '--max-jobs' => 20,
                '--max-time' => 25,
                '--tries' => 3,
            ]);
        });

        return response($ran === false ? 'SKIPPED' : 'OK');
    }
}
