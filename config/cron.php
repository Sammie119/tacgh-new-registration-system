<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cron/Queue Trigger Secret
    |--------------------------------------------------------------------------
    |
    | A random, hard-to-guess token used to authorize GET requests to the
    | /tasks/run-queue/{secret} endpoint, which drains the notification
    | queue. Intended to be pinged periodically by an external webcron
    | service on hosts without shell/cron access. Generate one with
    | `php artisan tinker --execute="echo Str::random(40);"` and set it
    | as CRON_SECRET in .env. Left empty, the endpoint always 404s.
    |
    */
    'secret' => env('CRON_SECRET'),
];
