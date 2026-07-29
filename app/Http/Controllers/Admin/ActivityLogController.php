<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            $isAdmin = $user && (
                (method_exists($user, 'hasRole') && $user->hasRole('admin')) ||
                ($user->role ?? null) === 'admin' ||
                ($user->is_admin ?? false) === true
            );
            abort_unless($isAdmin, 403);
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = Activity::with('causer')
            ->latest();

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $activities = $query->paginate(25)->withQueryString();

        $logNames = Activity::query()->select('log_name')->distinct()->pluck('log_name');
        $events   = Activity::query()->select('event')->distinct()->pluck('event');

        return view('admin.activity_logs.index', compact('activities', 'logNames', 'events'));
    }

    public function show(Activity $activity)
    {
        $activity->load('causer', 'subject');
        return view('admin.activity_logs.show', compact('activity'));
    }
}
