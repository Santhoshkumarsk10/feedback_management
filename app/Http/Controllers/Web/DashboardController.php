<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\User;
use App\Models\Visit;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'organizers' => User::where('role', 'organizer')->count(),
            // unique visitors (by mobile when given, otherwise by name)
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

        $leaders = User::where('role', 'organizer')
            ->withCount(['visitsAsOrganizer as visits_count', 'feedbacksReceived as feedbacks_count'])
            ->withAvg('feedbacksReceived as avg_rating', 'overall_rating')
            ->orderByDesc('avg_rating')->get();

        $recent = Feedback::with(['visit', 'organizer'])->latest('submitted_at')->limit(8)->get();
        $low = Feedback::with(['visit', 'organizer'])->where('overall_rating', '<=', 2)
            ->latest('submitted_at')->limit(5)->get();

        return view('dashboard', compact('stats', 'distribution', 'months', 'leaders', 'recent', 'low'));
    }
}
