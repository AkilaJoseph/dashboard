<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        $query = AuditLog::with('user')->latest('created_at');

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('event', 'like', "%{$search}%")
                  ->orWhere('auditable_type', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($event = request('event')) {
            $query->where('event', $event);
        }

        $logs   = $query->paginate(50)->withQueryString();
        $events = AuditLog::select('event')->distinct()->orderBy('event')->pluck('event');

        return view('admin.audit-logs.index', compact('logs', 'events'));
    }
}
