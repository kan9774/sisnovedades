<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny-log');

        $logs = Activity::with('causer')
            ->when($request->filled('log_name'), fn($q) => $q->where('log_name', $request->log_name))
            ->when($request->filled('event'), fn($q) => $q->where('event', $request->event))
            ->when($request->filled('user_id'), fn($q) => $q->where('causer_id', $request->user_id))
            ->when($request->filled('desde'), fn($q) => $q->whereDate('created_at', '>=', $request->desde))
            ->when($request->filled('hasta'), fn($q) => $q->whereDate('created_at', '<=', $request->hasta))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $logNames = Activity::distinct()->pluck('log_name')->filter();
        $eventos = Activity::distinct()->pluck('event')->filter();

        return view('admin.logs.index', compact('logs', 'logNames', 'eventos'));
    }
}