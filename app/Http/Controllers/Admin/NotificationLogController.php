<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\NotificationLogService;
use Illuminate\Http\Request;

class NotificationLogController extends Controller
{
    private NotificationLogService $notificationLogService;

    public function __construct(NotificationLogService $notificationLogService)
    {
        $this->notificationLogService = $notificationLogService;
    }

    public function index(Request $request)
    {
        return $this->notificationLogService->index(
            get_logged_in_user_event_id(),
            $request->get('channel'),
            $request->get('status')
        );
    }
}
