<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Feedback;
use App\Models\Shift;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentShift = Shift::current();
        $currentUser = auth()->user();

        // Today's Operations Queue & Shift Filter
        $todayTab = $request->input('today_tab', 'pending');
        $shiftFilter = $request->input('shift_id');
        $scope = $request->input('scope', 'all'); // 'all' or 'my'

        $todayBase = Visit::with(['organizer', 'feedback', 'shift'])
            ->whereDate('visit_date', today())
            ->when($shiftFilter, fn ($q, $s) => $q->where('shift_id', $s))
            ->when($scope === 'my' && $currentUser, fn ($q) => $q->where('organizer_id', $currentUser->id));

        $allScopeBase = Visit::whereDate('visit_date', today())
            ->when($shiftFilter, fn ($q, $s) => $q->where('shift_id', $s));

        $myScopeBase = Visit::whereDate('visit_date', today())
            ->when($shiftFilter, fn ($q, $s) => $q->where('shift_id', $s))
            ->when($currentUser, fn ($q) => $q->where('organizer_id', $currentUser->id));

        $todayCounts = [
            'all' => (clone $todayBase)->count(),
            'pending' => (clone $todayBase)->doesntHave('feedback')->count(),
            'completed' => (clone $todayBase)->has('feedback')->count(),
            'scope_all' => (clone $allScopeBase)->count(),
            'scope_my' => (clone $myScopeBase)->count(),
        ];

        // If 'pending' tab is requested but 0 pending and completed exists, switch to all or completed
        if ($todayTab === 'pending' && $todayCounts['pending'] === 0 && $todayCounts['all'] > 0 && ! $request->has('today_tab')) {
            $todayTab = 'all';
        }

        $todayVisits = (clone $todayBase)
            ->when($todayTab === 'pending', function ($q) {
                // Priority to checked out visitors, then oldest exit time first
                $q->doesntHave('feedback')
                  ->orderByRaw('CASE WHEN out_time IS NOT NULL THEN 0 ELSE 1 END')
                  ->orderBy('out_time', 'asc')
                  ->latest('id');
            })
            ->when($todayTab === 'completed', fn ($q) => $q->has('feedback')->latest('id'))
            ->when($todayTab === 'all', fn ($q) => $q->latest('id'))
            ->get();

        $shifts = Shift::active()->orderBy('start_time')->get();

        // Today Summary Metrics (per requirement Section 6.5)
        $todayTotalVisitors = $todayCounts['all'];
        $todayGivenCount = $todayCounts['completed'];
        $todayPendingCount = $todayCounts['pending'];
        $todayGivenPercent = $todayTotalVisitors > 0 ? round(($todayGivenCount / $todayTotalVisitors) * 100) : 0;
        $todayPendingPercent = $todayTotalVisitors > 0 ? round(($todayPendingCount / $todayTotalVisitors) * 100) : 0;
        $todayAvgRating = Feedback::whereHas('visit', fn ($v) => $v->whereDate('visit_date', today()))->avg('overall_rating');
        $lastSyncAt = AuditLog::where('module', 'visitors')->where('action', 'sync')->latest('created_at')->value('created_at');

        // Overall Analytics & Historical Metrics
        $stats = [
            'organizers' => User::whereIn('role', ['organizer', 'staff'])->count(),
            'visitors' => (int) Visit::selectRaw('COUNT(DISTINCT COALESCE(visitor_mobile, visitor_name)) as c')->value('c'),
            'visits' => Visit::count(),
            'feedbacks' => Feedback::count(),
            'avg' => round((float) Feedback::avg('overall_rating'), 2),
            'low' => Feedback::where('overall_rating', '<=', 2)->count(),
            'today_total' => $todayTotalVisitors,
            'today_given' => $todayGivenCount,
            'today_given_pct' => $todayGivenPercent,
            'today_pending' => $todayPendingCount,
            'today_pending_pct' => $todayPendingPercent,
            'today_avg' => $todayAvgRating ? round((float) $todayAvgRating, 1) : null,
            'last_sync_at' => $lastSyncAt,
        ];

        $dist = Feedback::whereNotNull('overall_rating')
            ->selectRaw('overall_rating, COUNT(*) as c')->groupBy('overall_rating')->pluck('c', 'overall_rating');
        $distribution = collect(range(1, 5))->map(fn ($i) => (int) ($dist[$i] ?? 0))->all();

        $months = Visit::where('visit_date', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(visit_date, '%Y-%m') as ym, COUNT(*) as c")
            ->groupBy('ym')->orderBy('ym')->pluck('c', 'ym');

        $leaders = User::whereIn('role', ['organizer', 'staff'])
            ->withCount(['visitsAsOrganizer as visits_count', 'feedbacksReceived as feedbacks_count'])
            ->withAvg('feedbacksReceived as avg_rating', 'overall_rating')
            ->orderByDesc('avg_rating')->get();

        $recent = Feedback::with(['visit', 'organizer', 'submittedBy'])->latest('submitted_at')->limit(8)->get();
        $low = Feedback::with(['visit', 'organizer', 'submittedBy'])->where('overall_rating', '<=', 2)
            ->latest('submitted_at')->limit(5)->get();

        return view('dashboard', compact(
            'stats', 'distribution', 'months', 'leaders', 'recent', 'low',
            'currentShift', 'todayVisits', 'todayCounts', 'todayTab', 'currentUser', 'shifts', 'scope'
        ));
    }

    /**
     * Real-time polling live feed endpoint (refreshes counts every 30-60 sec).
     */
    public function liveFeed(Request $request)
    {
        $currentShift = Shift::current();
        $currentUser = auth()->user();
        $shiftFilter = $request->input('shift_id');
        $scope = $request->input('scope', 'all');

        $todayBase = Visit::whereDate('visit_date', today())
            ->when($shiftFilter, fn ($q, $s) => $q->where('shift_id', $s))
            ->when($scope === 'my' && $currentUser, fn ($q) => $q->where('organizer_id', $currentUser->id));

        $counts = [
            'all' => (clone $todayBase)->count(),
            'pending' => (clone $todayBase)->doesntHave('feedback')->count(),
            'completed' => (clone $todayBase)->has('feedback')->count(),
        ];

        $todayAvgRating = Feedback::whereHas('visit', fn ($v) => $v->whereDate('visit_date', today()))->avg('overall_rating');
        $lastSyncAt = AuditLog::where('module', 'visitors')->where('action', 'sync')->latest('created_at')->value('created_at');

        return response()->json([
            'success' => true,
            'counts' => $counts,
            'today_avg' => $todayAvgRating ? round((float) $todayAvgRating, 1) : null,
            'last_sync_formatted' => $lastSyncAt ? $lastSyncAt->diffForHumans() : 'Not yet synced',
            'current_shift' => $currentShift ? [
                'name' => $currentShift->name,
                'range' => $currentShift->formatted_24h_range,
                'progress' => $currentShift->shift_progress,
            ] : null,
        ]);
    }

    /**
     * Export Pending Visitors list directly to CSV.
     */
    public function exportPending(Request $request)
    {
        $currentUser = auth()->user();
        $shiftFilter = $request->input('shift_id');
        $scope = $request->input('scope', 'all');

        $pendingVisits = Visit::with(['organizer', 'shift'])
            ->whereDate('visit_date', today())
            ->doesntHave('feedback')
            ->when($shiftFilter, fn ($q, $s) => $q->where('shift_id', $s))
            ->when($scope === 'my' && $currentUser, fn ($q) => $q->where('organizer_id', $currentUser->id))
            ->orderByRaw('CASE WHEN out_time IS NOT NULL THEN 0 ELSE 1 END')
            ->orderBy('out_time', 'asc')
            ->latest('id')
            ->get();

        $filename = 'pending-visitors-' . today()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($pendingVisits) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Visitor ID',
                'Visitor Name',
                'Company',
                'Mobile',
                'Designation',
                'Duty Shift',
                'Host Staff',
                'Department',
                'In Time',
                'Out Time',
                'Checkout Status',
                'Time Since Exit',
                'Priority Alert',
            ]);

            foreach ($pendingVisits as $v) {
                $status = $v->out_time ? 'Checked Out' : 'Inside Plant';
                $timeSinceExit = $v->time_since_exit ?? '—';
                $alert = $v->is_exceeded_exit_threshold ? 'HIGH PRIORITY (>2 Hours After Exit)' : ($v->out_time ? 'Checked Out (Pending)' : 'Normal');

                fputcsv($out, [
                    $v->visitor_code ?: ('VIS-' . $v->id),
                    $v->visitor_name,
                    $v->visitor_company ?: '—',
                    $v->visitor_mobile ?: '—',
                    $v->visitor_designation ?: '—',
                    $v->shift?->name ?: '—',
                    $v->organizer?->name ?: 'Unassigned',
                    $v->department ?: ($v->organizer?->department ?: '—'),
                    $v->formatted_in_time ?: '—',
                    $v->formatted_out_time ?: '—',
                    $status,
                    $timeSinceExit,
                    $alert,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
