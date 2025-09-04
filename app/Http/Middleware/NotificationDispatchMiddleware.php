<?php

namespace App\Http\Middleware;

use Artisan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationDispatchMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $job = DB::table('jobs')->count();

        if($job >= 1){
//            Artisan::call('schedule:run');
            Artisan::call('queue:work --stop-when-empty');
        }

        return $next($request);
    }
}
