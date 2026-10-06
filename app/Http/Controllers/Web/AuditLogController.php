<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'all');

        $query = AuditLog::with('user')
            ->when($tab === 'masters', fn ($q) => $q->whereIn('module', ['plants', 'roles', 'users', 'questions']))
            ->when($tab === 'security', fn ($q) => $q->where('module', 'auth'))
            ->when($tab === 'operations', fn ($q) => $q->whereIn('module', ['visits', 'feedbacks', 'reports']))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->action))
            ->when($request->filled('module'), fn ($q) => $q->where('module', $request->module))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $v = $request->q;
                $w->where('user_name', 'like', "%$v%")
                  ->orWhere('description', 'like', "%$v%")
                  ->orWhere('ip_address', 'like', "%$v%")
                  ->orWhere('action', 'like', "%$v%")
                  ->orWhere('module', 'like', "%$v%");
            }));

        $totalCount = AuditLog::count();
        $mastersCount = AuditLog::whereIn('module', ['plants', 'roles', 'users', 'questions'])->count();
        $securityCount = AuditLog::where('module', 'auth')->count();
        $operationsCount = AuditLog::whereIn('module', ['visits', 'feedbacks', 'reports'])->count();

        $logs = $query->latest('id')->paginate(10)->withQueryString();

        return view('audit_logs.index', [
            'logs' => $logs,
            'tabCounts' => [
                'all' => $totalCount,
                'masters' => $mastersCount,
                'security' => $securityCount,
                'operations' => $operationsCount,
            ],
            'currentTab' => $tab,
        ]);
    }

    public function show(AuditLog $auditLog)
    {
        return response()->json($auditLog);
    }
}
