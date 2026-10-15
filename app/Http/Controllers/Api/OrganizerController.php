<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Feedback;
use App\Models\Question;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrganizerController extends Controller
{
    public function dashboard()
    {
        $id = auth('api')->id();
        $feedback = fn () => Feedback::where('organizer_id', $id);

        $todayBase = Visit::where(function ($q) use ($id) {
            $q->where('organizer_id', $id)->orWhereNull('organizer_id');
        })->whereDate('visit_date', today());

        $todayCounts = [
            'all' => (clone $todayBase)->count(),
            'pending' => (clone $todayBase)->doesntHave('feedback')->count(),
            'completed' => (clone $todayBase)->has('feedback')->count(),
        ];

        $todayVisitors = (clone $todayBase)
            ->with(['feedback'])
            ->latest('id')
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'name' => $v->visitor_name,
                    'company' => $v->visitor_company,
                    'designation' => $v->visitor_designation,
                    'mobile' => $v->visitor_mobile,
                    'email' => $v->visitor_email,
                    'purpose' => $v->purpose,
                    'visitor_code' => $v->visitor_code,
                    'status' => $v->feedback ? 'completed' : 'pending',
                    'is_completed' => ! is_null($v->feedback),
                    'rating' => $v->feedback?->overall_rating,
                    'feedback_id' => $v->feedback?->id,
                ];
            });

        return [
            'today_counts' => $todayCounts,
            'today_visitors' => $todayVisitors,
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
            'recent_feedback' => $feedback()->with('visit.visitor')
                ->latest('submitted_at')->limit(5)
                ->get(['id', 'visit_id', 'overall_rating', 'comments', 'submitted_at']),
        ];
    }

    /** Everyone this organizer showed around. ?from=&to=&q=&tab=&today= */
    public function visits(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'tab' => 'nullable|string|in:all,pending,completed',
            'today' => 'nullable|boolean',
            'q' => ['nullable', 'string', 'max:100', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
        ]);

        $tab = $request->input('tab', 'all');
        $isToday = $request->boolean('today');
        $id = auth('api')->id();

        return Visit::where(function ($q) use ($id) {
                $q->where('organizer_id', $id)->orWhereNull('organizer_id');
            })
            ->with([
                'feedback:id,visit_id,overall_rating,submitted_at',
            ])
            ->when($isToday, fn ($q) => $q->whereDate('visit_date', today()))
            ->when(! $isToday && $request->from, fn ($q, $v) => $q->whereDate('visit_date', '>=', $v))
            ->when(! $isToday && $request->to, fn ($q, $v) => $q->whereDate('visit_date', '<=', $v))
            ->when($tab === 'pending', fn ($q) => $q->doesntHave('feedback'))
            ->when($tab === 'completed', fn ($q) => $q->has('feedback'))
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('visitor_code', 'like', "%$v%")
                ->orWhereHas('visitor', fn ($vq) => $vq
                    ->where('name', 'like', "%$v%")
                    ->orWhere('company', 'like', "%$v%"))))
            ->latest('visit_date')->latest('id')->paginate(20);
    }

    /** Poll & sync visitors from external 3rd-party API */
    public function syncVisitors(Request $request, \App\Services\ExternalVisitorSyncService $syncService)
    {
        $result = $syncService->syncVisitors(
            $request->input('date'),
            auth('api')->id()
        );

        return response()->json($result);
    }

    /** One feedback with every question + answer. */
    public function feedbackDetail(int $id)
    {
        return Feedback::where('organizer_id', auth('api')->id())
            ->with([
                'visit:id,visitor_id,visitor_code,visit_date,purpose',
                'visit.visitor',
                'answers.question:id,question,type',
            ])->findOrFail($id);
    }

    /** Submit feedback on behalf of a visitor. */
    public function submitFeedbackOnBehalf(Request $request, Visit $visit)
    {
        if ($visit->feedback) {
            abort(422, 'Feedback already submitted for this visit.');
        }

        if ($visit->is_feedback_expired) {
            abort(422, 'Feedback submission has expired for this visit. Feedback must be submitted on the day of the visit.');
        }

        $data = $request->validate([
            'plant_id' => 'required|integer|exists:plants,id',
            'organizer_id' => 'nullable|integer|exists:users,id',
            'overall_rating' => 'required|integer|between:1,5',
            'comments' => 'nullable|string|max:2000',
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer' => 'nullable',
            'answers.*.reason' => 'nullable',
        ]);

        $organizer = auth('api')->user();

        if (!$organizer->is_active) {
            abort(403, 'Your staff account is inactive.');
        }

        $feedback = DB::transaction(function () use ($data, $visit, $organizer) {
            $organizerId = $data['organizer_id'] ?? $visit->organizer_id ?? $organizer->id;

            $feedback = Feedback::create([
                'visit_id' => $visit->id,
                'plant_id' => $data['plant_id'],
                'organizer_id' => $organizerId,
                'overall_rating' => $data['overall_rating'],
                'comments' => $data['comments'] ?? null,
                'submitted_mode' => 'staff_assisted',
                'submitted_by_staff_id' => $organizer->id,
                'submitted_at' => now(),
            ]);

            $visit->update([
                'plant_id' => $data['plant_id'],
                'organizer_id' => $organizerId,
            ]);

            foreach ($data['answers'] as $ans) {
                $val = is_array($ans['answer'] ?? null)
                    ? implode(', ', $ans['answer'])
                    : ($ans['answer'] ?? null);

                $feedback->answers()->create([
                    'question_id' => $ans['question_id'],
                    'answer' => $val,
                    'reason' => $ans['reason'] ?? null,
                ]);
            }

            AuditLog::record(
                'create',
                'feedbacks',
                "Organizer {$organizer->name} submitted evaluation on behalf of {$visit->visitor_name} ({$visit->visitor_company}) via API",
                ['visit_id' => $visit->id, 'rating' => $feedback->overall_rating],
                $organizer
            );

            return $feedback;
        });

        return response()->json([
            'success' => true,
            'message' => "Feedback recorded successfully on behalf of {$visit->visitor_name}!",
            'feedback_id' => $feedback->id,
        ], 201);
    }
}
