<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\AuditLog;
use App\Models\WhatsappLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MonitoringController extends Controller
{
    public function loginLogs(Request $request)
    {
        $logs = LoginLog::with('user')
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Monitoring/LoginLogs', ['logs' => $logs]);
    }

    public function activityLogs(Request $request)
    {
        $query = AuditLog::with(['user.personel', 'user.role']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('model_type', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('personel', function($p) use ($search) {
                            $p->where('full_name', 'like', "%{$search}%")
                              ->orWhere('nikc', 'like', "%{$search}%")
                              ->orWhere('matra', 'like', "%{$search}%");
                        });
                  });
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Admin/Monitoring/ActivityLogs', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'action'])
        ]);
    }

    public function whatsappLogs()
    {
        $logs = WhatsappLog::latest()->paginate(15);
        return Inertia::render('Admin/Monitoring/WhatsappLogs', ['logs' => $logs]);
    }
}