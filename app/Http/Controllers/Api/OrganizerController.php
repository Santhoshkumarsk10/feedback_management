<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Feedback;
use App\Models\Question;
use App\Models\Shift;
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

        $currentShift = Shift::current();

        $todayBase = Visit::where(function ($q) use ($id) {
            $q->where('organizer_id', $id)->orWhereNull('organizer_id');
        })->whereDate('visit_date', today());

        $todayCounts = [
            'all' => (clone $todayBase)->count(),
            'pending' => (clone $todayBase)->doesntHave('feedback')->count(),
            'completed' => (clone $todayBase)->has('feedback')->count(),
        ];

        $todayVisitors = (clone $todayBase)
            ->with(['shift', 'feedback'])
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
                    'shift_name' => $v->shift?->name,
                    'shift_range' => $v->shift?->formatted_24h_range,
                    'visitor_code' => $v->visitor_code,
                    'status' => $v->feedback ? 'completed' : 'pending',
                    'is_completed' => ! is_null($v->feedback),
                    'rating' => $v->feedback?->overall_rating,
                    'feedback_id' => $v->feedback?->id,
                ];
            });

        return [
            'current_shift' => $currentShift ? [
                'id' => $currentShift->id,
                'name' => $currentShift->name,
                'code' => $currentShift->code,
                'start_time' => $currentShift->start_time_short,
                'end_time' => $currentShift->end_time_short,
                'formatted_range' => $currentShift->formatted_24h_range,
                'progress' => $currentShift->shift_progress,
            ] : null,
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
            'recent_feedback' => $feedback()->with('visit:id,visitor_name,visitor_company')
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
                'shift:id,name,code,start_time,end_time',
            ])
            ->when($isToday, fn ($q) => $q->whereDate('visit_date', today()))
            ->when(! $isToday && $request->from, fn ($q, $v) => $q->whereDate('visit_date', '>=', $v))
            ->when(! $isToday && $request->to, fn ($q, $v) => $q->whereDate('visit_date', '<=', $v))
            ->when($tab === 'pending', fn ($q) => $q->doesntHave('feedback'))
            ->when($tab === 'completed', fn ($q) => $q->has('feedback'))
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('visitor_name', 'like', "%$v%")
                ->orWhere('visitor_company', 'like', "%$v%")
                ->orWhere('visitor_code', 'like', "%$v%")))
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
                'visit:id,visitor_name,visitor_mobile,visitor_company,visitor_email,visit_date,purpose,shift_id',
                'visit.shift:id,name,code,start_time,end_time',
                'answers.question:id,question,type',
            ])->findOrFail($id);
    }

    /** Submit feedback on behalf of a visitor. */
    public function submitFeedbackOnBehalf(Request $request, Visit $visit)
    {
        if ($visit->feedback) {
            abort(422, 'Feedback already submitted for this visit.');
        }

        $data = $request->validate([
            'overall_rating' => 'required|integer|between:1,5',
            'comments' => 'nullable|string|max:2000',
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer' => 'nullable',
        ]);

        $organizer = auth('api')->user();

        if (!$organizer->isOnDuty()) {
            abort(403, 'Not in current shift. You cannot submit assisted feedback while off duty.');
        }

        $feedback = DB::transaction(function () use ($data, $visit, $organizer) {
            $feedback = Feedback::create([
                'visit_id' => $visit->id,
                'organizer_id' => $visit->organizer_id ?: $organizer->id,
                'overall_rating' => $data['overall_rating'],
                'comments' => $data['comments'] ?? null,
                'submitted_mode' => 'staff_assisted',
                'submitted_by_staff_id' => $organizer->id,
                'submitted_at' => now(),
            ]);

            foreach ($data['answers'] as $ans) {
                $val = is_array($ans['answer'] ?? null)
                    ? implode(', ', $ans['answer'])
                    : ($ans['answer'] ?? null);

                $feedback->answers()->create([
                    'question_id' => $ans['question_id'],
                    'answer' => $val,
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
