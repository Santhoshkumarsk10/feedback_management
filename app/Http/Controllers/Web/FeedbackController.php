<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Plant;
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
            'plant_id' => 'nullable|integer|exists:plants,id',
            'tier' => 'nullable|string|in:all,5_star,4_star,critical',
            'per_page' => 'nullable|integer',
        ]);

        $tier = $request->input('tier', 'all');
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $query = Feedback::with(['visit', 'organizer', 'plant'])
            ->when($request->plant_id, fn ($q, $v) => $q->where('plant_id', $v))
            ->when($request->organizer_id, fn ($q, $v) => $q->where('organizer_id', $v))
            ->when($request->rating, fn ($q, $v) => $q->where('overall_rating', $v))
            ->when($tier === '5_star', fn ($q) => $q->where('overall_rating', 5))
            ->when($tier === '4_star', fn ($q) => $q->where('overall_rating', 4))
            ->when($tier === 'critical', fn ($q) => $q->where('overall_rating', '<=', 3))
            ->when($request->from, fn ($q, $v) => $q->whereDate('submitted_at', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->whereDate('submitted_at', '<=', $v));

        $baseCount = Feedback::when($request->plant_id, fn ($q, $v) => $q->where('plant_id', $v))
            ->when($request->organizer_id, fn ($q, $v) => $q->where('organizer_id', $v))
            ->when($request->from, fn ($q, $v) => $q->whereDate('submitted_at', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->whereDate('submitted_at', '<=', $v));

        $totalCount = min((clone $baseCount)->count(), 1000);
        $star5Count = min((clone $baseCount)->where('overall_rating', 5)->count(), 1000);
        $star4Count = min((clone $baseCount)->where('overall_rating', 4)->count(), 1000);
        $criticalCount = min((clone $baseCount)->where('overall_rating', '<=', 3)->count(), 1000);

        $feedbacks = $query->latest('submitted_at')->paginateCapped($perPage, 1000);

        return view('feedbacks.index', [
            'feedbacks' => $feedbacks,
            'organizers' => User::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'plants' => Plant::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
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
        $feedback->load(['visit', 'organizer', 'submittedBy', 'plant', 'answers.question']);

        return view('feedbacks.show', compact('feedback'));
    }
}
