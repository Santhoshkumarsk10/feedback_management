<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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

        $todayBase = Visit::with(['organizer', 'feedback', 'shift'])
            ->whereDate('visit_date', today())
            ->when($shiftFilter, fn ($q, $s) => $q->where('shift_id', $s));

        $todayCounts = [
            'all' => (clone $todayBase)->count(),
            'pending' => (clone $todayBase)->doesntHave('feedback')->count(),
            'completed' => (clone $todayBase)->has('feedback')->count(),
        ];

        // If 'pending' tab is requested but 0 pending and completed exists, switch to all or completed
        if ($todayTab === 'pending' && $todayCounts['pending'] === 0 && $todayCounts['all'] > 0 && ! $request->has('today_tab')) {
            $todayTab = 'all';
        }

        $todayVisits = (clone $todayBase)
            ->when($todayTab === 'pending', fn ($q) => $q->doesntHave('feedback'))
            ->when($todayTab === 'completed', fn ($q) => $q->has('feedback'))
            ->latest('id')
            ->get();

        $shifts = Shift::active()->orderBy('start_time')->get();

        // Overall Analytics & Metrics
        $stats = [
            'organizers' => User::whereIn('role', ['organizer', 'staff'])->count(),
            'visitors' => (int) Visit::selectRaw('COUNT(DISTINCT COALESCE(visitor_mobile, visitor_name)) as c')->value('c'),
            'visits' => Visit::count(),
            'feedbacks' => Feedback::count(),
            'avg' => round((float) Feedback::avg('overall_rating'), 2),
            'low' => Feedback::where('overall_rating', '<=', 2)->count(),
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

        $recent = Feedback::with(['visit', 'organizer'])->latest('submitted_at')->limit(8)->get();
        $low = Feedback::with(['visit', 'organizer'])->where('overall_rating', '<=', 2)
            ->latest('submitted_at')->limit(5)->get();

        return view('dashboard', compact(
            'stats', 'distribution', 'months', 'leaders', 'recent', 'low',
            'currentShift', 'todayVisits', 'todayCounts', 'todayTab', 'currentUser', 'shifts'
        ));
    }
}
