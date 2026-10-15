<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Feedback;
use App\Models\FeedbackAnswer;
use App\Models\Question;
use App\Models\User;
use App\Models\Visit;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $now = now();

        // 1. Date Range Handling
        $selectedRange = $request->input('range', 'month');
        $isAllTime = false;
        $startDate = null;
        $endDate = null;
        $prevStartDate = null;
        $prevEndDate = null;
        $growthSubtext = 'vs last month';
        $rangeLabel = '';

        if ($request->filled('from') && $request->filled('to')) {
            try {
                $startDate = Carbon::parse($request->input('from'))->startOfDay();
                $endDate = Carbon::parse($request->input('to'))->endOfDay();
                $diffDays = $startDate->diffInDays($endDate) + 1;
                $prevStartDate = (clone $startDate)->subDays($diffDays);
                $prevEndDate = (clone $endDate)->subDays($diffDays);
                $selectedRange = 'custom';
                $rangeLabel = $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y');
                $growthSubtext = 'vs prev ' . $diffDays . 'd';
            } catch (\Throwable $e) {
                $selectedRange = 'month';
            }
        }

        if (!$startDate && !$isAllTime) {
            switch ($selectedRange) {
                case 'today':
                    $startDate = (clone $now)->startOfDay();
                    $endDate = (clone $now)->endOfDay();
                    $prevStartDate = (clone $now)->subDay()->startOfDay();
                    $prevEndDate = (clone $now)->subDay()->endOfDay();
                    $rangeLabel = $now->format('d M Y');
                    $growthSubtext = 'vs yesterday';
                    break;

                case '7days':
                    $startDate = (clone $now)->subDays(6)->startOfDay();
                    $endDate = (clone $now)->endOfDay();
                    $prevStartDate = (clone $now)->subDays(13)->startOfDay();
                    $prevEndDate = (clone $now)->subDays(7)->endOfDay();
                    $rangeLabel = $startDate->format('d M') . ' - ' . $endDate->format('d M Y');
                    $growthSubtext = 'vs prev 7 days';
                    break;

                case '30days':
                    $startDate = (clone $now)->subDays(29)->startOfDay();
                    $endDate = (clone $now)->endOfDay();
                    $prevStartDate = (clone $now)->subDays(59)->startOfDay();
                    $prevEndDate = (clone $now)->subDays(30)->endOfDay();
                    $rangeLabel = $startDate->format('d M') . ' - ' . $endDate->format('d M Y');
                    $growthSubtext = 'vs prev 30 days';
                    break;

                case 'all':
                    $isAllTime = true;
                    $rangeLabel = 'All Time Records';
                    $growthSubtext = 'overall';
                    break;

                case 'month':
                default:
                    $selectedRange = 'month';
                    $startDate = (clone $now)->startOfMonth();
                    $endDate = (clone $now)->endOfMonth();
                    $prevStartDate = (clone $now)->subMonth()->startOfMonth();
                    $prevEndDate = (clone $now)->subMonth()->endOfMonth();
                    $rangeLabel = $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y');
                    $growthSubtext = 'vs last month';
                    break;
            }
        }

        // Today Summary Metrics for executive status banner
        $todayTotalVisitors = Visit::whereDate('visit_date', today())->count();
        $todayGivenCount = Visit::whereDate('visit_date', today())->has('feedback')->count();
        $todayPendingCount = Visit::whereDate('visit_date', today())->doesntHave('feedback')->count();
        $todayGivenPercent = $todayTotalVisitors > 0 ? round(($todayGivenCount / $todayTotalVisitors) * 100) : 0;
        $todayPendingPercent = $todayTotalVisitors > 0 ? round(($todayPendingCount / $todayTotalVisitors) * 100) : 0;
        $todayAvgRating = Feedback::whereHas('visit', fn ($v) => $v->whereDate('visit_date', today()))->avg('overall_rating');
        $lastSyncAt = AuditLog::where('module', 'visitors')->where('action', 'sync')->latest('created_at')->value('created_at');

        // Dynamic Filtering Query Builders
        $visitsQuery = Visit::query();
        $feedbacksQuery = Feedback::query();

        if (!$isAllTime && $startDate && $endDate) {
            $visitsQuery->whereBetween('visit_date', [$startDate->toDateString(), $endDate->toDateString()]);
            $feedbacksQuery->whereBetween('submitted_at', [$startDate, $endDate]);
        }

        $totalVisitorsCount = (clone $visitsQuery)->count();
        $totalFeedbacksCount = (clone $feedbacksQuery)->count();
        $overallAvg = (clone $feedbacksQuery)->avg('overall_rating') ? round((float) (clone $feedbacksQuery)->avg('overall_rating'), 1) : 0.0;
        $positiveCount = (clone $feedbacksQuery)->where('overall_rating', '>=', 4)->count();
        $positivePct = $totalFeedbacksCount > 0 ? round(($positiveCount / $totalFeedbacksCount) * 100) : 0;
        $pendingActionsCount = (clone $visitsQuery)->doesntHave('feedback')->count();

        // Growth metrics vs Previous Period
        if ($prevStartDate && $prevEndDate) {
            $prevVisitors = Visit::whereBetween('visit_date', [$prevStartDate->toDateString(), $prevEndDate->toDateString()])->count();
            $visitorsGrowth = $prevVisitors > 0 ? round((($totalVisitorsCount - $prevVisitors) / $prevVisitors) * 100) : ($totalVisitorsCount > 0 ? 100 : 0);

            $prevFeedbacks = Feedback::whereBetween('submitted_at', [$prevStartDate, $prevEndDate])->count();
            $feedbackGrowth = $prevFeedbacks > 0 ? round((($totalFeedbacksCount - $prevFeedbacks) / $prevFeedbacks) * 100) : ($totalFeedbacksCount > 0 ? 100 : 0);

            $prevAvg = Feedback::whereBetween('submitted_at', [$prevStartDate, $prevEndDate])->avg('overall_rating');
            if ($prevAvg !== null && $overallAvg > 0) {
                $ratingDiff = round($overallAvg - (float) $prevAvg, 1);
                $ratingGrowthText = ($ratingDiff >= 0 ? '+' : '') . $ratingDiff . ' pts';
            } elseif ($overallAvg > 0) {
                $ratingGrowthText = '+' . $overallAvg . ' pts';
            } else {
                $ratingGrowthText = '+0.0 pts';
            }

            $prevPending = Visit::whereBetween('visit_date', [$prevStartDate->toDateString(), $prevEndDate->toDateString()])->doesntHave('feedback')->count();
            $pendingGrowth = $prevPending > 0 ? round((($pendingActionsCount - $prevPending) / $prevPending) * 100) : ($pendingActionsCount > 0 ? 100 : 0);
        } else {
            $visitorsGrowth = 0;
            $feedbackGrowth = 0;
            $ratingGrowthText = '+0.0 pts';
            $pendingGrowth = 0;
        }

        $stats = [
            'organizers' => User::whereIn('role', ['organizer', 'staff'])->count(),
            'visitors' => $totalVisitorsCount,
            'visits' => Visit::count(),
            'feedbacks' => $totalFeedbacksCount,
            'avg' => $overallAvg,
            'low' => Feedback::where('overall_rating', '<=', 2)->count(),
            'today_total' => $todayTotalVisitors,
            'today_given' => $todayGivenCount,
            'today_given_pct' => $todayGivenPercent,
            'today_pending' => $todayPendingCount,
            'today_pending_pct' => $todayPendingPercent,
            'today_avg' => $todayAvgRating ? round((float) $todayAvgRating, 1) : null,
            'last_sync_at' => $lastSyncAt,
        ];

        // 5 Modern Key Metric Cards matching Reference UI
        $saasMetrics = [
            'total_visitors' => [
                'value' => number_format($totalVisitorsCount),
                'growth' => ($visitorsGrowth >= 0 ? '+' : '') . $visitorsGrowth . '%',
                'subtext' => $growthSubtext,
            ],
            'total_feedbacks' => [
                'value' => number_format($totalFeedbacksCount),
                'growth' => ($feedbackGrowth >= 0 ? '+' : '') . $feedbackGrowth . '%',
                'subtext' => $growthSubtext,
            ],
            'average_rating' => [
                'value' => ($totalFeedbacksCount > 0 ? number_format($overallAvg, 1) : '0.0') . ' / 5',
                'growth' => $ratingGrowthText,
                'subtext' => $growthSubtext,
            ],
            'positive_feedbacks' => [
                'value' => number_format($positiveCount),
                'growth' => "{$positivePct}%",
                'subtext' => 'of total feedbacks',
            ],
            'pending_actions' => [
                'value' => number_format($pendingActionsCount),
                'growth' => ($pendingGrowth >= 0 ? '+' : '') . $pendingGrowth . '%',
                'subtext' => $growthSubtext,
            ],
        ];

        // Trends Builder: Pre-builds 30, 14, and 7 day trends for instant client-side switching
        $buildTrends = function ($daysCount, $step = 1) use ($now) {
            $labels = [];
            $total = [];
            $positive = [];

            $points = ceil($daysCount / $step);
            for ($i = $points - 1; $i >= 0; $i--) {
                $pointEnd = (clone $now)->subDays($i * $step)->endOfDay();
                $pointStart = (clone $pointEnd)->subDays($step - 1)->startOfDay();

                $labels[] = $pointEnd->format('d M');
                $cnt = Feedback::whereBetween('submitted_at', [$pointStart, $pointEnd])->count();
                $pos = Feedback::whereBetween('submitted_at', [$pointStart, $pointEnd])->where('overall_rating', '>=', 4)->count();

                $total[] = $cnt;
                $positive[] = $pos;
            }

            return [
                'labels' => $labels,
                'total' => $total,
                'positive' => $positive,
            ];
        };

        $allTrends = [
            '30' => $buildTrends(30, 4),
            '14' => $buildTrends(14, 1),
            '7'  => $buildTrends(7, 1),
        ];

        $trendsData = $allTrends['30'];

        // Rating Distribution Donut
        $dist = (clone $feedbacksQuery)->whereNotNull('overall_rating')
            ->selectRaw('overall_rating, COUNT(*) as c')->groupBy('overall_rating')->pluck('c', 'overall_rating');

        $c5 = (int) ($dist[5] ?? 0);
        $c4 = (int) ($dist[4] ?? 0);
        $c3 = (int) ($dist[3] ?? 0);
        $c2 = (int) ($dist[2] ?? 0);
        $c1 = (int) ($dist[1] ?? 0);
        $sumRatings = $c5 + $c4 + $c3 + $c2 + $c1;
        $denomRatings = max(1, $sumRatings);

        $ratingDist = [
            'total' => $sumRatings,
            'items' => [
                ['label' => '5 Star', 'count' => $c5, 'percent' => $sumRatings > 0 ? round(($c5 / $denomRatings) * 100) : 0, 'color' => '#2563eb'],
                ['label' => '4 Star', 'count' => $c4, 'percent' => $sumRatings > 0 ? round(($c4 / $denomRatings) * 100) : 0, 'color' => '#38bdf8'],
                ['label' => '3 Star', 'count' => $c3, 'percent' => $sumRatings > 0 ? round(($c3 / $denomRatings) * 100) : 0, 'color' => '#f59e0b'],
                ['label' => '2 Star', 'count' => $c2, 'percent' => $sumRatings > 0 ? round(($c2 / $denomRatings) * 100) : 0, 'color' => '#fb923c'],
                ['label' => '1 Star', 'count' => $c1, 'percent' => $sumRatings > 0 ? round(($c1 / $denomRatings) * 100) : 0, 'color' => '#ef4444'],
            ],
        ];

        // Dynamic Feedback by Category from questions in database
        $sec4Questions = Question::where('section', 'like', '%Section 4%')
            ->orderBy('id')
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
        foreach ($sec4Questions as $question) {
            $shortName = $cleanCatNames[$question->question] ?? str_replace('Facilities Rating: ', '', $question->question);
            
            $ansQuery = FeedbackAnswer::where('question_id', $question->id);
            if (!$isAllTime && $startDate && $endDate) {
                $ansQuery->whereHas('feedback', fn ($fq) => $fq->whereBetween('submitted_at', [$startDate, $endDate]));
            }
            
            $cnt = $ansQuery->count();
            $avg = $cnt > 0 ? (float) $ansQuery->avg('answer') : 0.0;
            $percent = $cnt > 0 ? min(100, round(($avg / 5.0) * 100)) : 0;

            $categoryFeedback[] = [
                'name' => $shortName,
                'count' => $cnt,
                'percent' => $percent,
                'avg' => $avg,
                'color' => $catColors[$catIdx % count($catColors)],
            ];
            $catIdx++;
        }

        // Top Locations / Plant Facilities from visits table
        $locQuery = Visit::leftJoin('users', 'visits.organizer_id', '=', 'users.id');
        if (!$isAllTime && $startDate && $endDate) {
            $locQuery->whereBetween('visits.visit_date', [$startDate->toDateString(), $endDate->toDateString()]);
        }
        $totalLocVisits = (clone $locQuery)->count() ?: 1;

        $locRows = $locQuery->selectRaw('COALESCE(NULLIF(visits.department,""), NULLIF(users.department,""), "Main Technical Centre") as loc_name, count(*) as count')
            ->groupBy('loc_name')
            ->orderByDesc('count')
            ->limit(6)
            ->get();

        $locColors = ['#2563eb', '#10b981', '#f59e0b', '#f97316', '#8b5cf6', '#a855f7'];
        $topLocations = [];
        $rank = 1;
        foreach ($locRows as $loc) {
            $pct = round(($loc->count / $totalLocVisits) * 100);
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
        $recentQuery = Feedback::with(['visit.visitor', 'organizer']);
        if (!$isAllTime && $startDate && $endDate) {
            $recentQuery->whereBetween('submitted_at', [$startDate, $endDate]);
        }
        $recentFeedbacksList = $recentQuery->latest('submitted_at')->limit(5)->get();

        if ($recentFeedbacksList->isEmpty() && Feedback::exists()) {
            $recentFeedbacksList = Feedback::with(['visit.visitor', 'organizer'])->latest('submitted_at')->limit(5)->get();
        }

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
                'date_time' => $f->submitted_at ? $f->submitted_at->diffForHumans() : 'Recently',
                'full_date' => $f->submitted_at ? $f->submitted_at->format('d M Y, h:i A') : '—',
                'rating' => (int) ($f->overall_rating ?? 5),
                'comment' => $f->comments ? Str::limit($f->comments, 65) : 'Visitor feedback submitted.',
                'url' => route('feedbacks.show', $f),
            ];
        });

        // Visitor Types segmentation
        $periodVisitorsCount = $totalVisitorsCount;
        $trialsCount = (clone $visitsQuery)->where(function($q) {
            $q->where('purpose', 'like', '%Trial%')->orWhere('purpose', 'like', '%Demo%');
        })->count();
        $discussionCount = (clone $visitsQuery)->where(function($q) {
            $q->where('purpose', 'like', '%Discussion%')->orWhere('purpose', 'like', '%Evaluation%')->orWhere('purpose', 'like', '%Meeting%');
        })->count();
        $auditCount = (clone $visitsQuery)->where(function($q) {
            $q->where('purpose', 'like', '%Inspection%')->orWhere('purpose', 'like', '%Audit%');
        })->count();
        $maintCount = (clone $visitsQuery)->where(function($q) {
            $q->where('purpose', 'like', '%Maintenance%')->orWhere('purpose', 'like', '%Review%')->orWhere('purpose', 'like', '%Assessment%');
        })->count();
        $toursCount = (clone $visitsQuery)->where(function($q) {
            $q->where('purpose', 'like', '%Tour%')->orWhere('purpose', 'like', '%VR%');
        })->count();
        $otherPurposesCount = max(0, $periodVisitorsCount - ($trialsCount + $discussionCount + $auditCount + $maintCount + $toursCount));
        $sumVisitorSegments = max(1, $periodVisitorsCount);

        $visitorTypes = [
            'total' => $periodVisitorsCount,
            'items' => [
                ['label' => 'Audits & Compliance',     'percent' => $periodVisitorsCount > 0 ? round(($auditCount / $sumVisitorSegments) * 100) : 0, 'count' => $auditCount, 'color' => '#2563eb'],
                ['label' => 'Technical Discussion',    'percent' => $periodVisitorsCount > 0 ? round(($discussionCount / $sumVisitorSegments) * 100) : 0, 'count' => $discussionCount, 'color' => '#10b981'],
                ['label' => 'Maintenance & Reviews',   'percent' => $periodVisitorsCount > 0 ? round(($maintCount / $sumVisitorSegments) * 100) : 0, 'count' => $maintCount, 'color' => '#f59e0b'],
                ['label' => 'Machine Trials & Demo',   'percent' => $periodVisitorsCount > 0 ? round(($trialsCount / $sumVisitorSegments) * 100) : 0, 'count' => $trialsCount, 'color' => '#f97316'],
                ['label' => 'Plant & VR Tours',        'percent' => $periodVisitorsCount > 0 ? round(($toursCount / $sumVisitorSegments) * 100) : 0, 'count' => $toursCount, 'color' => '#8b5cf6'],
                ['label' => 'General / Other Visits',  'percent' => $periodVisitorsCount > 0 ? round(($otherPurposesCount / $sumVisitorSegments) * 100) : 0, 'count' => $otherPurposesCount, 'color' => '#64748b'],
            ],
        ];

        // Dynamic Feedback Status from database
        $reviewedCount = $totalFeedbacksCount;
        $pendingCount = $pendingActionsCount;
        $escalatedQuery = Feedback::where('overall_rating', '<=', 2);
        if (!$isAllTime && $startDate && $endDate) {
            $escalatedQuery->whereBetween('submitted_at', [$startDate, $endDate]);
        }
        $escalatedCount = $escalatedQuery->count();
        $sumStatus = $reviewedCount + $pendingCount + $escalatedCount;
        $denomStatus = max(1, $sumStatus);

        $feedbackStatuses = [
            'total' => $sumStatus,
            'items' => [
                ['label' => 'Reviewed',  'percent' => $sumStatus > 0 ? round(($reviewedCount / $denomStatus) * 100) : 0, 'count' => $reviewedCount, 'color' => '#2563eb'],
                ['label' => 'Pending',   'percent' => $sumStatus > 0 ? round(($pendingCount / $denomStatus) * 100) : 0, 'count' => $pendingCount, 'color' => '#f59e0b'],
                ['label' => 'Escalated', 'percent' => $sumStatus > 0 ? round(($escalatedCount / $denomStatus) * 100) : 0, 'count' => $escalatedCount, 'color' => '#ef4444'],
            ],
        ];

        $todayCounts = [
            'all' => $todayTotalVisitors,
            'pending' => $todayPendingCount,
            'completed' => $todayGivenCount,
        ];

        return view('dashboard', compact(
            'stats', 'saasMetrics', 'trendsData', 'allTrends', 'ratingDist', 'categoryFeedback',
            'topLocations', 'recentFeedbacks', 'visitorTypes', 'feedbackStatuses',
            'currentUser', 'todayCounts',
            'selectedRange', 'rangeLabel'
        ));
    }

    /**
     * Unified Global Search for Visitors, Feedbacks, and Staff Directory
     */
    public function globalSearch(Request $request)
    {
        $term = trim($request->input('q', ''));
        if (strlen($term) < 2) {
            return response()->json([
                'success' => true,
                'results' => [
                    'visitors' => [],
                    'feedbacks' => [],
                    'staff' => [],
                ],
                'total' => 0,
            ]);
        }

        // 1. Visitors & Visits
        $visits = Visit::with(['visitor', 'feedback'])
            ->where(function ($q) use ($term) {
                $q->where('visitor_code', 'like', "%{$term}%")
                  ->orWhere('department', 'like', "%{$term}%")
                  ->orWhere('purpose', 'like', "%{$term}%")
                  ->orWhereHas('visitor', function ($vq) use ($term) {
                      $vq->where('name', 'like', "%{$term}%")
                         ->orWhere('company', 'like', "%{$term}%")
                         ->orWhere('mobile', 'like', "%{$term}%")
                         ->orWhere('email', 'like', "%{$term}%");
                  });
            })
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(function ($v) {
                $hasFeedback = $v->feedback()->exists();
                $name = $v->visitor_name ?? ('Visitor #' . $v->id);
                return [
                    'id' => $v->id,
                    'title' => $name,
                    'company' => $v->visitor_company ?: 'Company —',
                    'code' => $v->visitor_code,
                    'department' => $v->department ?: '—',
                    'date' => $v->visit_date ? $v->visit_date->format('d M Y') : '—',
                    'has_feedback' => $hasFeedback,
                    'url' => $hasFeedback 
                        ? route('feedbacks.show', $v->feedback->id) 
                        : route('visits.feedback.create', $v->id),
                    'action_label' => $hasFeedback ? 'View Feedback' : 'Give Feedback',
                ];
            });

        // 2. Feedbacks
        $feedbacks = Feedback::with('visit.visitor')
            ->where(function ($q) use ($term) {
                $q->where('comments', 'like', "%{$term}%")
                  ->orWhereHas('visit.visitor', function ($vq) use ($term) {
                      $vq->where('name', 'like', "%{$term}%")
                         ->orWhere('company', 'like', "%{$term}%");
                  });
            })
            ->latest('submitted_at')
            ->limit(5)
            ->get()
            ->map(function ($f) {
                return [
                    'id' => $f->id,
                    'title' => $f->visit?->visitor_name ?? 'Guest Feedback',
                    'rating' => (int) $f->overall_rating,
                    'comments' => Str::limit($f->comments ?? 'No comments provided', 50),
                    'date' => $f->submitted_at ? $f->submitted_at->format('d M Y') : 'Recently',
                    'url' => route('feedbacks.show', $f->id),
                ];
            });

        // 3. Staff / Hosts
        $staff = User::where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('department', 'like', "%{$term}%");
            })
            ->limit(4)
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'title' => $u->name,
                    'department' => $u->department ?? 'Staff',
                    'role' => ucfirst($u->role ?? 'User'),
                    'email' => $u->email,
                    'url' => route('users.index', ['search' => $u->name]),
                ];
            });

        $totalCount = $visits->count() + $feedbacks->count() + $staff->count();

        return response()->json([
            'success' => true,
            'results' => [
                'visitors' => $visits,
                'feedbacks' => $feedbacks,
                'staff' => $staff,
            ],
            'total' => $totalCount,
        ]);
    }

    /**
     * Dedicated Operations Page: Today's Plant Visitors & Feedback Queue
     */
    public function shiftQueue(Request $request)
    {
        $currentUser = auth()->user();

        // Today's Operations Queue Filter
        $todayTab = $request->input('today_tab', 'pending');
        $scope = $request->input('scope', 'all'); // 'all' or 'my'
        $search = $request->input('search');

        $todayBase = Visit::with(['organizer', 'feedback'])
            ->whereDate('visit_date', today())
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

        // Today Summary Metrics
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

        return view('shift_queue', compact(
            'stats', 'todayVisits', 'todayCounts', 'todayTab',
            'currentUser', 'scope', 'search'
        ));
    }

    /**
     * Real-time polling live feed endpoint (refreshes counts every 30-60 sec).
     */
    public function liveFeed(Request $request)
    {
        $currentUser = auth()->user();
        $scope = $request->input('scope', 'all');

        $todayBase = Visit::whereDate('visit_date', today())
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
        ]);
    }

    /**
     * Export Pending Visitors list directly to CSV.
     */
    public function exportPending(Request $request)
    {
        $currentUser = auth()->user();
        $scope = $request->input('scope', 'all');

        $pendingVisits = Visit::with(['organizer'])
            ->whereDate('visit_date', today())
            ->doesntHave('feedback')
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
