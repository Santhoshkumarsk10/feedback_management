<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'tab' => 'nullable|string|in:all,masters,security,operations',
            'action' => 'nullable|string|max:50',
            'module' => 'nullable|string|max:50',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'per_page' => 'nullable|integer',
            'q' => ['nullable', 'string', 'max:100', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
        ]);

        $tab = $request->input('tab', 'all');
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

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

        $totalCount = min(AuditLog::count(), 1000);
        $mastersCount = min(AuditLog::whereIn('module', ['plants', 'roles', 'users', 'questions'])->count(), 1000);
        $securityCount = min(AuditLog::where('module', 'auth')->count(), 1000);
        $operationsCount = min(AuditLog::whereIn('module', ['visits', 'feedbacks', 'reports'])->count(), 1000);

        $logs = $query->latest('id')->paginateCapped($perPage, 1000);

        $auditSuggestions = AuditLog::select('id', 'user_name', 'action', 'module', 'description', 'ip_address')
            ->latest('id')
            ->take(30)
            ->get()
            ->map(function ($l) {
                return [
                    'code' => strtoupper($l->action ?? $l->module),
                    'name' => $l->description ?? ($l->action . ' on ' . $l->module),
                    'sub' => ($l->user_name ?? 'System') . ($l->ip_address ? ' • ' . $l->ip_address : ''),
                    'value' => $l->description ?? $l->action,
                ];
            });

        return view('audit_logs.index', [
            'logs' => $logs,
            'tabCounts' => [
                'all' => $totalCount,
                'masters' => $mastersCount,
                'security' => $securityCount,
                'operations' => $operationsCount,
            ],
            'currentTab' => $tab,
            'auditSuggestions' => $auditSuggestions,
        ]);
    }

    public function show(AuditLog $auditLog)
    {
        return response()->json($auditLog);
    }
}
