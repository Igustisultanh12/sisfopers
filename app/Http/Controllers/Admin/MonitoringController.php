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

    public function activityLogs()
    {
        $logs = AuditLog::with('user')
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Monitoring/ActivityLogs', ['logs' => $logs]);
    }

    public function whatsappLogs()
    {
        $logs = WhatsappLog::latest()->paginate(15);
        return Inertia::render('Admin/Monitoring/WhatsappLogs', ['logs' => $logs]);
    }
}