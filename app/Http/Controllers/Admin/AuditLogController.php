<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')->orderByDesc('id');

        $search = $request->get('search');
        if (! empty($search)) {
            $query->where('description', 'like', "%{$search}%");
        }

        $logName = $request->get('log_name');
        if (! empty($logName)) {
            $query->where('log_name', $logName);
        }

        $data['activities'] = $query->paginate(50)->withQueryString();
        $data['search'] = $search;
        $data['logName'] = $logName;
        $data['logNames'] = Activity::query()->distinct()->pluck('log_name')->filter()->values();

        return view('admin.audit_log.index', $data);
    }
}
