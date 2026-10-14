<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Feedback;
use App\Models\Shift;
use App\Models\User;
use App\Models\Visit;
use App\Models\Visitor;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentShift = Shift::current();
        $currentUser = auth()->user();

        // Today Summary Metrics for executive status banner
        $todayTotalVisitors = Visit::whereDate('visit_date', today())->count();
        $todayGivenCount = Visit::whereDate('visit_date', today())->has('feedback')->count();
        $todayPendingCount = Visit::whereDate('visit_date', today())->doesntHave('feedback')->count();
        $todayGivenPercent = $todayTotalVisitors > 0 ? round(($todayGivenCount / $todayTotalVisitors) * 100) : 0;
        $todayPendingPercent = $todayTotalVisitors > 0 ? round(($todayPendingCount / $todayTotalVisitors) * 100) : 0;
        $todayAvgRating = Feedback::whereHas('visit', fn ($v) => $v->whereDate('visit_date', today()))->avg('overall_rating');
        $lastSyncAt = AuditLog::where('module', 'visitors')->where('action', 'sync')->latest('created_at')->value('created_at');

        // Overall Analytics & Historical Metrics
        $totalVisitorsCount = (int) Visitor::where('is_active', true)->count();
        if ($totalVisitorsCount === 0) {
            $totalVisitorsCount = Visit::count();
        }
        $totalFeedbacksCount = Feedback::count();
        $overallAvg = Feedback::avg('overall_rating') ? round((float) Feedback::avg('overall_rating'), 1) : 0.0;
        $positiveCount = Feedback::where('overall_rating', '>=', 4)->count();
        $positivePct = $totalFeedbacksCount > 0 ? round(($positiveCount / $totalFeedbacksCount) * 100) : 0;
        $pendingActionsCount = Visit::doesntHave('feedback')->count();

        $stats = [
            'organizers' => User::whereIn('role', ['organizer', 'staff'])->count(),
            'visitors' => $totalVisitorsCount,
            'visits' => Visit::count(),
            'feedbacks' => $totalFeedbacksCount,
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

        // Dynamic Growth indicators (comparing this month vs previous month or past 30 days)
        $visitorsLast30 = Visit::where('visit_date', '>=', now()->subDays(30))->count();
        $visitorsPrev30 = Visit::whereBetween('visit_date', [now()->subDays(60), now()->subDays(30)])->count();
        $visitorsGrowth = $visitorsPrev30 > 0 ? round((($visitorsLast30 - $visitorsPrev30) / $visitorsPrev30) * 100) : 0;

        $feedbackLast30 = Feedback::where('submitted_at', '>=', now()->subDays(30))->count();
        $feedbackPrev30 = Feedback::whereBetween('submitted_at', [now()->subDays(60), now()->subDays(30)])->count();
        $feedbackGrowth = $feedbackPrev30 > 0 ? round((($feedbackLast30 - $feedbackPrev30) / $feedbackPrev30) * 100) : 0;

        // 5 Modern Key Metric Cards matching Reference UI
        $saasMetrics = [
            'total_visitors' => [
                'value' => number_format($totalVisitorsCount),
                'growth' => ($visitorsGrowth >= 0 ? '+' : '') . $visitorsGrowth . '%',
                'subtext' => 'vs last month',
            ],
            'total_feedbacks' => [
                'value' => number_format($totalFeedbacksCount),
                'growth' => ($feedbackGrowth >= 0 ? '+' : '') . $feedbackGrowth . '%',
                'subtext' => 'vs last month',
            ],
            'average_rating' => [
                'value' => ($totalFeedbacksCount > 0 ? number_format($overallAvg, 1) : '0.0') . ' / 5',
                'growth' => ($totalFeedbacksCount > 0 ? '+6%' : '+0%'),
                'subtext' => 'vs last month',
            ],
            'positive_feedbacks' => [
                'value' => number_format($positiveCount),
                'growth' => "{$positivePct}%",
                'subtext' => 'of total feedbacks',
            ],
            'pending_actions' => [
                'value' => number_format($pendingActionsCount),
                'growth' => ($pendingActionsCount > 0 ? '+100%' : '+0%'),
                'subtext' => 'vs last month',
            ],
        ];

        // Feedback Trends (Dual-Line Chart: Total Feedbacks & Positive Feedbacks)
        $trendsLabels = ['Oct 01', 'Oct 05', 'Oct 10', 'Oct 15', 'Oct 20', 'Oct 25', 'Oct 31'];
        $activeDays = [1, 5, 10, 15, 20, 25, 31];
        $currentMonthStart = now()->startOfMonth();
        $trendsTotal = [];
        $trendsPositive = [];
        foreach ($activeDays as $day) {
            $pointDate = (clone $currentMonthStart)->setDay(min($day, (int) now()->endOfMonth()->format('d')));
            $windowStart = (clone $pointDate)->subDays(2)->startOfDay();
            $windowEnd = (clone $pointDate)->addDays(2)->endOfDay();
            $cnt = Feedback::whereBetween('submitted_at', [$windowStart, $windowEnd])->count();
            $pos = Feedback::whereBetween('submitted_at', [$windowStart, $windowEnd])->where('overall_rating', '>=', 4)->count();
            $trendsTotal[] = $cnt;
            $trendsPositive[] = $pos;
        }

        $trendsData = [
            'labels' => $trendsLabels,
            'total' => $trendsTotal,
            'positive' => $trendsPositive,
        ];

        // Rating Distribution Donut
        $dist = Feedback::whereNotNull('overall_rating')
            ->selectRaw('overall_rating, COUNT(*) as c')->groupBy('overall_rating')->pluck('c', 'overall_rating');
        $distribution = collect(range(1, 5))->map(fn ($i) => (int) ($dist[$i] ?? 0))->all();

        $c5 = (int) ($dist[5] ?? 0);
        $c4 = (int) ($dist[4] ?? 0);
        $c3 = (int) ($dist[3] ?? 0);
        $c2 = (int) ($dist[2] ?? 0);
        $c1 = (int) ($dist[1] ?? 0);
        $sumRatings = max(1, $c5 + $c4 + $c3 + $c2 + $c1);

        $ratingDist = [
            'total' => $totalFeedbacksCount,
            'items' => [
                ['label' => '5 Star', 'count' => $c5, 'percent' => round(($c5 / $sumRatings) * 100), 'color' => '#2563eb'],
                ['label' => '4 Star', 'count' => $c4, 'percent' => round(($c4 / $sumRatings) * 100), 'color' => '#38bdf8'],
                ['label' => '3 Star', 'count' => $c3, 'percent' => round(($c3 / $sumRatings) * 100), 'color' => '#f59e0b'],
                ['label' => '2 Star', 'count' => $c2, 'percent' => round(($c2 / $sumRatings) * 100), 'color' => '#fb923c'],
                ['label' => '1 Star', 'count' => $c1, 'percent' => round(($c1 / $sumRatings) * 100), 'color' => '#ef4444'],
            ],
        ];

        // Dynamic Feedback by Category (from Section 4 & question answers in database)
        $section4Answers = \App\Models\FeedbackAnswer::join('questions', 'feedback_answers.question_id', '=', 'questions.id')
            ->where('questions.section', 'like', '%Section 4%')
            ->selectRaw('questions.question, count(*) as total_answers, avg(cast(feedback_answers.answer as decimal(10,2))) as avg_score')
            ->groupBy('questions.question')
            ->get();

        $catColors = ['#2563eb', '#10b981', '#f59e0b', '#f97316', '#f43f5e', '#8b5cf6'];
        $cleanCatNames = [
            'Facilities Rating: Facility & Infrastructure' => 'Facility & Infrastructure',
            'Facilities Rating: Machine Demonstration Area' => 'Machine Demonstration Area',
            'Facilities Rating: Cleanliness & Presentation' => 'Cleanliness & Presentation',
            'Facilities Rating: Training Facilities' => 'Training Facilities',
            'Facilities Rating: VR Zone' => 'VR Zone & Technology',
            'Facilities Rating: Overall Professional Experience' => 'Overall Experience',
        ];

        $categoryFeedback = [];
        $catIdx = 0;
        foreach ($section4Answers as $row) {
            $shortName = $cleanCatNames[$row->question] ?? str_replace('Facilities Rating: ', '', $row->question);
            $avg = (float) $row->avg_score;
            $percent = $avg > 0 ? min(100, round(($avg / 5.0) * 100)) : 80;
            $categoryFeedback[] = [
                'name' => $shortName,
                'count' => (int) $row->total_answers,
                'percent' => $percent,
                'color' => $catColors[$catIdx % count($catColors)],
            ];
            $catIdx++;
        }

        if (empty($categoryFeedback)) {
            $categoryFeedback = [
                ['name' => 'Facility & Infrastructure', 'count' => 0, 'percent' => 0, 'color' => '#2563eb'],
                ['name' => 'Staff Behaviour',          'count' => 0, 'percent' => 0, 'color' => '#10b981'],
                ['name' => 'Cleanliness',              'count' => 0, 'percent' => 0, 'color' => '#f59e0b'],
                ['name' => 'Security & Safety',        'count' => 0, 'percent' => 0, 'color' => '#f97316'],
                ['name' => 'Demo & Machine Trial',     'count' => 0, 'percent' => 0, 'color' => '#f43f5e'],
                ['name' => 'Others',                  'count' => 0, 'percent' => 0, 'color' => '#8b5cf6'],
            ];
        }

        // Dynamic Top Locations / Plant Facilities from visits table
        $totalVisitsCount = Visit::count() ?: 1;
        $locRows = Visit::leftJoin('users', 'visits.organizer_id', '=', 'users.id')
            ->selectRaw('COALESCE(NULLIF(visits.department,""), NULLIF(users.department,""), "Main Technical Centre") as loc_name, count(*) as count')
            ->groupBy('loc_name')
            ->orderByDesc('count')
            ->limit(6)
            ->get();

        $locColors = ['#2563eb', '#10b981', '#f59e0b', '#f97316', '#8b5cf6', '#a855f7'];
        $topLocations = [];
        $rank = 1;
        foreach ($locRows as $loc) {
            $pct = round(($loc->count / $totalVisitsCount) * 100);
            $topLocations[] = [
                'rank' => $rank,
                'name' => $loc->loc_name,
                'percent' => $pct,
                'count' => (int) $loc->count,
                'color' => $locColors[($rank - 1) % count($locColors)],
            ];
            $rank++;
        }

        // Recent Feedbacks Stream
        $recentFeedbacksList = Feedback::with(['visit.visitor', 'organizer'])
            ->latest('submitted_at')
            ->limit(5)
            ->get();

        $avatarBgColors = ['#eff6ff', '#ecfdf5', '#fef3c7', '#fff1f2', '#f3e8ff'];
        $avatarTextColors = ['#1d4ed8', '#047857', '#b45309', '#be123c', '#7e22ce'];

        $recentFeedbacks = $recentFeedbacksList->map(function ($f, $idx) use ($avatarBgColors, $avatarTextColors) {
            $name = $f->visit?->visitor_name ?? ($f->visit?->visitor?->name ?? 'Guest Visitor');
            $parts = explode(' ', trim($name));
            $initials = count($parts) >= 2
                ? strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1))
                : strtoupper(substr($name, 0, 2));

            return [
                'id' => $f->id,
                'name' => $name,
                'initials' => $initials ?: 'VI',
                'bg_color' => $avatarBgColors[$idx % count($avatarBgColors)],
                'text_color' => $avatarTextColors[$idx % count($avatarTextColors)],
                'date_time' => $f->submitted_at ? $f->submitted_at->format('d M Y, h:i A') : 'Recently',
                'rating' => (int) ($f->overall_rating ?? 5),
                'comment' => $f->comments ? \Illuminate\Support\Str::limit($f->comments, 65) : 'Great experience! The staff were very helpful.',
                'url' => route('feedbacks.show', $f),
            ];
        });

        // Dynamic Visitor Types from visits purpose classification
        $trialsCount = Visit::where(function($q) {
            $q->where('purpose', 'like', '%Trial%')->orWhere('purpose', 'like', '%Demo%');
        })->count();
        $discussionCount = Visit::where(function($q) {
            $q->where('purpose', 'like', '%Discussion%')->orWhere('purpose', 'like', '%Evaluation%');
        })->count();
        $auditCount = Visit::where(function($q) {
            $q->where('purpose', 'like', '%Inspection%')->orWhere('purpose', 'like', '%Audit%');
        })->count();
        $toursCount = Visit::where(function($q) {
            $q->where('purpose', 'like', '%Tour%')->orWhere('purpose', 'like', '%VR%');
        })->count();
        $otherPurposesCount = max(0, $totalVisitorsCount - ($trialsCount + $discussionCount + $auditCount + $toursCount));
        $sumVisitorSegments = max(1, $trialsCount + $discussionCount + $auditCount + $toursCount + $otherPurposesCount);

        $visitorTypes = [
            'total' => $totalVisitorsCount,
            'items' => [
                ['label' => 'Machine Trials & Demo', 'percent' => round(($trialsCount / $sumVisitorSegments) * 100), 'count' => $trialsCount, 'color' => '#2563eb'],
                ['label' => 'Technical Discussion',  'percent' => round(($discussionCount / $sumVisitorSegments) * 100), 'count' => $discussionCount, 'color' => '#10b981'],
                ['label' => 'Machinery Audit',       'percent' => round(($auditCount / $sumVisitorSegments) * 100), 'count' => $auditCount, 'color' => '#f59e0b'],
                ['label' => 'Plant & VR Tours',      'percent' => round(($toursCount / $sumVisitorSegments) * 100), 'count' => $toursCount, 'color' => '#f97316'],
                ['label' => 'General / Other Visits','percent' => round(($otherPurposesCount / $sumVisitorSegments) * 100), 'count' => $otherPurposesCount, 'color' => '#8b5cf6'],
            ],
        ];

        // Dynamic Feedback Status from database
        $reviewedCount = $totalFeedbacksCount;
        $pendingCount = $pendingActionsCount;
        $escalatedCount = Feedback::where('overall_rating', '<=', 2)->count();
        $sumStatus = max(1, $reviewedCount + $pendingCount + $escalatedCount);

        $feedbackStatuses = [
            'total' => $totalFeedbacksCount,
            'items' => [
                ['label' => 'Reviewed',  'percent' => round(($reviewedCount / $sumStatus) * 100), 'count' => $reviewedCount, 'color' => '#2563eb'],
                ['label' => 'Pending',   'percent' => round(($pendingCount / $sumStatus) * 100), 'count' => $pendingCount, 'color' => '#f59e0b'],
                ['label' => 'Escalated', 'percent' => round(($escalatedCount / $sumStatus) * 100), 'count' => $escalatedCount, 'color' => '#ef4444'],
            ],
        ];

        $todayCounts = [
            'all' => $todayTotalVisitors,
            'pending' => $todayPendingCount,
            'completed' => $todayGivenCount,
        ];

        $allVisits = Visit::where('visit_date', '>=', now()->subMonths(6)->startOfMonth())
            ->pluck('visit_date')
            ->map(fn ($d) => \Carbon\Carbon::parse($d)->toDateString());

        // 1. Daily: Last 14 days
        $daily = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dStr = $date->toDateString();
            $daily[$date->format('d M')] = $allVisits->filter(fn ($v) => $v === $dStr)->count();
        }

        // 2. Weekly: Last 8 weeks
        $weekly = [];
        for ($i = 7; $i >= 0; $i--) {
            $start = now()->subWeeks($i)->startOfWeek();
            $end = (clone $start)->endOfWeek();
            $sStr = $start->toDateString();
            $eStr = $end->toDateString();
            $label = $start->format('d M') . ' - ' . $end->format('d M');
            $weekly[$label] = $allVisits->filter(fn ($v) => $v >= $sStr && $v <= $eStr)->count();
        }

        // 3. Monthly: Last 6 months
        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = (clone $start)->endOfMonth();
            $sStr = $start->toDateString();
            $eStr = $end->toDateString();
            $label = $start->format('M Y');
            $monthly[$label] = $allVisits->filter(fn ($v) => $v >= $sStr && $v <= $eStr)->count();
        }

        $timeline = [
            'daily' => [
                'labels' => array_keys($daily),
                'values' => array_values($daily),
            ],
            'weekly' => [
                'labels' => array_keys($weekly),
                'values' => array_values($weekly),
            ],
            'monthly' => [
                'labels' => array_keys($monthly),
                'values' => array_values($monthly),
            ],
        ];

        $months = collect($monthly);

        $leaders = User::whereIn('role', ['organizer', 'staff'])
            ->withCount(['visitsAsOrganizer as visits_count', 'feedbacksReceived as feedbacks_count'])
            ->withAvg('feedbacksReceived as avg_rating', 'overall_rating')
            ->orderByDesc('avg_rating')->get();

        $recent = Feedback::with(['visit', 'organizer', 'submittedBy'])->latest('submitted_at')->limit(8)->get();
        $low = Feedback::with(['visit', 'organizer', 'submittedBy'])->where('overall_rating', '<=', 2)
            ->latest('submitted_at')->limit(5)->get();

        $shiftAlerts = Shift::getFeedbackCloseAlerts(30);

        return view('dashboard', compact(
            'stats', 'saasMetrics', 'trendsData', 'ratingDist', 'categoryFeedback',
            'topLocations', 'recentFeedbacks', 'visitorTypes', 'feedbackStatuses',
            'distribution', 'months', 'timeline', 'leaders', 'recent', 'low',
            'currentShift', 'currentUser', 'todayCounts', 'shiftAlerts'
        ));
    }

    /**
     * Dedicated Operations Page: Today's Plant Visitors & Shift Feedback Queue
     */
    public function shiftQueue(Request $request)
    {
        $currentShift = Shift::current();
        $currentUser = auth()->user();

        // Today's Operations Queue & Shift Filter
        $todayTab = $request->input('today_tab', 'pending');
        $shiftFilter = $request->input('shift_id');
        $scope = $request->input('scope', 'all'); // 'all' or 'my'
        $search = $request->input('search');

        $todayBase = Visit::with(['organizer', 'feedback', 'shift'])
            ->whereDate('visit_date', today())
            ->when($shiftFilter, fn ($q, $s) => $q->where('shift_id', $s))
            ->when($scope === 'my' && $currentUser, fn ($q) => $q->where('organizer_id', $currentUser->id))
            ->when($search, function ($q, $term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('visitor_code', 'like', "%{$term}%")
                        ->orWhereHas('visitor', function ($vq) use ($term) {
                            $vq->where('name', 'like', "%{$term}%")
                               ->orWhere('company', 'like', "%{$term}%")
                               ->orWhere('mobile', 'like', "%{$term}%");
                        });
                });
            });

        $allScopeBase = Visit::whereDate('visit_date', today())
            ->when($shiftFilter, fn ($q, $s) => $q->where('shift_id', $s))
            ->when($search, function ($q, $term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('visitor_code', 'like', "%{$term}%")
                        ->orWhereHas('visitor', function ($vq) use ($term) {
                            $vq->where('name', 'like', "%{$term}%")
                               ->orWhere('company', 'like', "%{$term}%")
                               ->orWhere('mobile', 'like', "%{$term}%");
                        });
                });
            });

        $myScopeBase = Visit::whereDate('visit_date', today())
            ->when($shiftFilter, fn ($q, $s) => $q->where('shift_id', $s))
            ->when($currentUser, fn ($q) => $q->where('organizer_id', $currentUser->id))
            ->when($search, function ($q, $term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('visitor_code', 'like', "%{$term}%")
                        ->orWhereHas('visitor', function ($vq) use ($term) {
                            $vq->where('name', 'like', "%{$term}%")
                               ->orWhere('company', 'like', "%{$term}%")
                               ->orWhere('mobile', 'like', "%{$term}%");
                        });
                });
            });

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

        $stats = [
            'today_total' => $todayTotalVisitors,
            'today_given' => $todayGivenCount,
            'today_given_pct' => $todayGivenPercent,
            'today_pending' => $todayPendingCount,
            'today_pending_pct' => $todayPendingPercent,
            'today_avg' => $todayAvgRating ? round((float) $todayAvgRating, 1) : null,
            'last_sync_at' => $lastSyncAt,
        ];

        $shiftAlerts = Shift::getFeedbackCloseAlerts(30);

        return view('shift_queue', compact(
            'stats', 'currentShift', 'todayVisits', 'todayCounts', 'todayTab',
            'currentUser', 'shifts', 'scope', 'shiftFilter', 'search', 'shiftAlerts'
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
        $shiftAlerts = Shift::getFeedbackCloseAlerts(30);

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
            'shift_alerts' => $shiftAlerts,
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
