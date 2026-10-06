<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Visit;
use Illuminate\Http\Request;

class OrganizerController extends Controller
{
    public function dashboard()
    {
        $id = auth('api')->id();
        $feedback = fn () => Feedback::where('organizer_id', $id);

        return [
            'total_visitors' => Visit::where('organizer_id', $id)->count(),
            'average_rating' => round((float) $feedback()->avg('overall_rating'), 2),
            'low_ratings' => $feedback()->where('overall_rating', '<=', 2)->count(),
            'rating_distribution' => $feedback()->whereNotNull('overall_rating')
                ->selectRaw('overall_rating, COUNT(*) as c')
                ->groupBy('overall_rating')->orderBy('overall_rating')->pluck('c', 'overall_rating'),
            'visits_per_month' => Visit::where('organizer_id', $id)
                ->where('visit_date', '>=', now()->subMonths(5)->startOfMonth())
                ->selectRaw("DATE_FORMAT(visit_date, '%Y-%m') as ym, COUNT(*) as c")
                ->groupBy('ym')->orderBy('ym')->pluck('c', 'ym'),
            'recent_feedback' => $feedback()->with('visit:id,visitor_name,visitor_company')
                ->latest('submitted_at')->limit(5)
                ->get(['id', 'visit_id', 'overall_rating', 'comments', 'submitted_at']),
        ];
    }

    /** Everyone this organizer showed around. ?from=&to=&q= */
    public function visits(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'q' => ['nullable', 'string', 'max:100', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
        ]);

        return Visit::where('organizer_id', auth('api')->id())
            ->with('feedback:id,visit_id,overall_rating,submitted_at')
            ->when($request->from, fn ($q, $v) => $q->whereDate('visit_date', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->whereDate('visit_date', '<=', $v))
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('visitor_name', 'like', "%$v%")->orWhere('visitor_company', 'like', "%$v%")))
            ->latest('visit_date')->latest('id')->paginate(20);
    }

    /** One feedback with every question + answer. */
    public function feedbackDetail(int $id)
    {
        return Feedback::where('organizer_id', auth('api')->id())
            ->with([
                'visit:id,visitor_name,visitor_mobile,visitor_company,visitor_email,visit_date,purpose',
                'answers.question:id,question,type',
            ])->findOrFail($id);
    }
}
