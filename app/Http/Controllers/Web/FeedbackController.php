<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'rating' => 'nullable|integer|between:1,5',
            'organizer_id' => 'nullable|integer|exists:users,id',
            'tier' => 'nullable|string|in:all,5_star,4_star,critical',
        ]);

        $tier = $request->input('tier', 'all');

        $query = Feedback::with(['visit', 'organizer'])
            ->when($request->organizer_id, fn ($q, $v) => $q->where('organizer_id', $v))
            ->when($request->rating, fn ($q, $v) => $q->where('overall_rating', $v))
            ->when($tier === '5_star', fn ($q) => $q->where('overall_rating', 5))
            ->when($tier === '4_star', fn ($q) => $q->where('overall_rating', 4))
            ->when($tier === 'critical', fn ($q) => $q->where('overall_rating', '<=', 3))
            ->when($request->from, fn ($q, $v) => $q->whereDate('submitted_at', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->whereDate('submitted_at', '<=', $v));

        $totalCount = Feedback::count();
        $star5Count = Feedback::where('overall_rating', 5)->count();
        $star4Count = Feedback::where('overall_rating', 4)->count();
        $criticalCount = Feedback::where('overall_rating', '<=', 3)->count();

        $feedbacks = $query->latest('submitted_at')->paginate(10)->withQueryString();

        return view('feedbacks.index', [
            'feedbacks' => $feedbacks,
            'organizers' => User::where('role', 'organizer')->orderBy('name')->get(['id', 'name']),
            'tabCounts' => [
                'all' => $totalCount,
                '5_star' => $star5Count,
                '4_star' => $star4Count,
                'critical' => $criticalCount,
            ],
            'currentTier' => $tier,
        ]);
    }

    public function show(Feedback $feedback)
    {
        $feedback->load(['visit', 'organizer', 'answers.question']);

        return view('feedbacks.show', compact('feedback'));
    }
}
