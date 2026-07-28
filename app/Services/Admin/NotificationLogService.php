<?php

namespace App\Services\Admin;

use App\Models\Admin\Dropdown;
use App\Models\NotificationLog;
use App\Models\RegistrantStage;

class NotificationLogService
{
    public function index($eventId, ?string $channel = null, ?string $status = null)
    {
        $query = NotificationLog::where('event_id', $eventId)->orderByDesc('id');

        if (! empty($channel)) {
            $query->where('channel', $channel);
        }

        if ($status === 'success') {
            $query->where('success', true);
        } elseif ($status === 'failed') {
            $query->where('success', false);
        }

        $data['logs'] = $query->paginate(50)->withQueryString();
        $data['channel'] = $channel;
        $data['status'] = $status;

        $regIds = $data['logs']->pluck('registrant_id')->filter()->unique();
        $stages = RegistrantStage::whereIn('id', $regIds)->get(['id', 'title', 'first_name', 'other_names', 'surname']);
        $titleIds = $stages->pluck('title')->filter()->unique();
        $dropdownNames = Dropdown::whereIn('id', $titleIds)->pluck('full_name', 'id');

        $data['registrant_names'] = $stages->mapWithKeys(function ($stage) use ($dropdownNames) {
            $name = trim(($dropdownNames[$stage->title] ?? '').' '.$stage->first_name.' '.$stage->other_names.' '.$stage->surname);

            return [$stage->id => strtoupper($name)];
        });

        return view('admin.notifications.index', $data);
    }
}
