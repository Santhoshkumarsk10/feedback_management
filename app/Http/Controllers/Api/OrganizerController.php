<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Feedback;
use App\Models\Plant;
use App\Models\Question;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrganizerController extends Controller
{
    public function dashboard()
    {
        $id = auth('api')->id();
        $user = auth('api')->user();

        if (! in_array($user?->role, ['superadmin', 'admin', 'coordinator'])) {
            abort(403, 'Forbidden: You do not have the required access role.');
        }

        $todayBase = Visit::whereDate('visit_date', today());

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
            'total_visitors' => Visit::count(),
            'average_rating' => round((float) (Feedback::avg('overall_rating') ?: 5.0), 2),
            'low_ratings' => Feedback::where('overall_rating', '<=', 2)->count(),
            'rating_distribution' => Feedback::whereNotNull('overall_rating')
                ->selectRaw('overall_rating, COUNT(*) as c')
                ->groupBy('overall_rating')->orderBy('overall_rating')->pluck('c', 'overall_rating'),
            'visits_per_month' => Visit::where('visit_date', '>=', now()->subMonths(5)->startOfMonth())
                ->selectRaw("DATE_FORMAT(visit_date, '%Y-%m') as ym, COUNT(*) as c")
                ->groupBy('ym')->orderBy('ym')->pluck('c', 'ym'),
            'recent_feedback' => Feedback::with('visit.visitor')
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
            'organizer_id' => 'nullable|integer|exists:users,id',
            'q' => ['nullable', 'string', 'max:100', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
        ]);

        $user = auth('api')->user();
        if (! in_array($user?->role, ['superadmin', 'admin', 'coordinator'])) {
            abort(403, 'Forbidden: You do not have the required access role.');
        }

        $tab = $request->input('tab', 'all');
        $isToday = $request->boolean('today');

        return Visit::with([
                'feedback:id,visit_id,overall_rating,submitted_at',
            ])
            ->when($request->filled('organizer_id'), fn ($q) => $q->where('organizer_id', $request->input('organizer_id')))
            ->when($isToday, fn ($q) => $q->whereDate('visit_date', today()))
            ->when(! $isToday && $request->filled('from'), fn ($q) => $q->whereDate('visit_date', '>=', $request->input('from')))
            ->when(! $isToday && $request->filled('to'), fn ($q) => $q->whereDate('visit_date', '<=', $request->input('to')))
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
        $user = auth('api')->user();
        if (! in_array($user?->role, ['superadmin', 'admin', 'coordinator'])) {
            abort(403, 'Forbidden: You do not have the required access role.');
        }

        $result = $syncService->syncVisitors(
            $request->input('date'),
            auth('api')->id()
        );

        return response()->json($result);
    }

    /** One feedback with every question + answer. */
    public function feedbackDetail(int $id)
    {
        $user = auth('api')->user();
        if (! in_array($user?->role, ['superadmin', 'admin', 'coordinator'])) {
            abort(403, 'Forbidden: You do not have the required access role.');
        }

        return Feedback::with([
            'visit:id,visitor_id,visitor_code,visit_date,purpose',
            'visit.visitor',
            'answers.question:id,question,type',
        ])->findOrFail($id);
    }

    /** Submit feedback on behalf of a visitor. */
    public function submitFeedbackOnBehalf(Request $request, Visit $visit)
    {
        if ($visit->feedback || $visit->feedback()->exists()) {
            abort(422, 'Feedback already submitted for this visit.');
        }

        if ($visit->is_feedback_expired) {
            abort(422, 'Feedback submission has expired for this visit. Feedback must be submitted on the day of the visit.');
        }

        $rawPlant = $request->input('plant_id')
            ?: $request->input('plant')
            ?: $request->input('facility')
            ?: $visit->plant_id
            ?: Plant::where('is_active', true)->value('id')
            ?: Plant::value('id');

        if (!empty($rawPlant)) {
            if (is_numeric($rawPlant) && Plant::where('id', (int) $rawPlant)->exists()) {
                $request->merge(['plant_id' => (int) $rawPlant]);
            } else {
                $plantStr = trim((string) $rawPlant);
                $plant = Plant::where('code', $plantStr)->first()
                    ?? Plant::where('name', $plantStr)->first()
                    ?? (preg_match('/PLANT-\d+/i', $plantStr, $m) ? Plant::where('code', strtoupper($m[0]))->first() : null)
                    ?? Plant::where('name', 'like', "%{$plantStr}%")->first();
                if ($plant) {
                    $request->merge(['plant_id' => $plant->id]);
                }
            }
        } elseif ($visit->plant_id) {
            $request->merge(['plant_id' => $visit->plant_id]);
        }

        // Normalize answers if passed as associative map [question_id => answer] or [question_id => [answer, reason]]
        $rawAnswers = $request->input('answers');
        if (is_array($rawAnswers) && !empty($rawAnswers)) {
            $firstVal = reset($rawAnswers);
            if (!is_array($firstVal) || !isset($firstVal['question_id'])) {
                $normalizedAnswers = [];
                foreach ($rawAnswers as $qId => $val) {
                    if (is_array($val)) {
                        $normalizedAnswers[] = [
                            'question_id' => $val['question_id'] ?? $qId,
                            'answer' => $val['answer'] ?? null,
                            'reason' => $val['reason'] ?? null,
                        ];
                    } else {
                        $normalizedAnswers[] = [
                            'question_id' => $qId,
                            'answer' => $val,
                            'reason' => null,
                        ];
                    }
                }
                $request->merge(['answers' => $normalizedAnswers]);
            }
        }

        // Normalize organizer_id if empty string
        if ($request->has('organizer_id') && empty($request->input('organizer_id'))) {
            $request->merge(['organizer_id' => null]);
        }

        $data = $request->validate([
            'plant_id' => 'required|integer|exists:plants,id',
            'organizer_id' => 'nullable|integer|exists:users,id',
            'overall_rating' => 'nullable|integer|between:1,5',
            'comments' => 'nullable|string|max:2000',
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer' => 'nullable',
            'answers.*.reason' => 'nullable',
        ]);

        $organizer = auth('api')->user() ?: $request->user();

        if (!$organizer) {
            abort(401, 'Unauthenticated.');
        }

        if (! in_array($organizer->role, ['superadmin', 'admin', 'coordinator'])) {
            abort(403, 'Forbidden: You do not have the required access role.');
        }

        if (!$organizer->is_active) {
            abort(403, 'Your account is inactive.');
        }

        // Calculate overall_rating if omitted or null
        $overallRating = $data['overall_rating'] ?? null;
        if (empty($overallRating)) {
            $ratings = collect($data['answers'])
                ->filter(fn ($a) => is_numeric($a['answer'] ?? null) && (int) $a['answer'] >= 1 && (int) $a['answer'] <= 5)
                ->pluck('answer');
            $overallRating = $ratings->isNotEmpty() ? (int) round($ratings->avg()) : 5;
        }

        $feedback = DB::transaction(function () use ($data, $visit, $organizer, $overallRating) {
            $organizerId = $data['organizer_id'] ?? $visit->organizer_id ?? $organizer->id;

            $feedback = Feedback::create([
                'visit_id' => $visit->id,
                'plant_id' => $data['plant_id'],
                'organizer_id' => $organizerId,
                'overall_rating' => $overallRating,
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

            $visitorName = $visit->visitor_name ?: ($visit->visitor?->name ?? 'Visitor');
            $company = $visit->visitor_company ?: ($visit->visitor?->company ?? '');
            $companyStr = $company ? " ({$company})" : '';

            AuditLog::record(
                'create',
                'feedbacks',
                "Organizer {$organizer->name} submitted evaluation on behalf of {$visitorName}{$companyStr} via API",
                ['visit_id' => $visit->id, 'rating' => $feedback->overall_rating],
                $organizer
            );

            return $feedback;
        });

        $visitorName = $visit->visitor_name ?: ($visit->visitor?->name ?? 'visitor');

        return response()->json([
            'success' => true,
            'message' => "Feedback recorded successfully on behalf of {$visitorName}!",
            'feedback_id' => $feedback->id,
        ], 201);
    }
}
