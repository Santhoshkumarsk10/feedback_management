<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Plant;
use App\Models\Question;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Public endpoints for the VISITOR app - no login. */
class VisitorController extends Controller
{
    /** Screen 1: list of organizers to pick from. */
    public function organizers()
    {
        return User::whereIn('role', ['organizer', 'staff'])->where('is_active', true)
            ->orderBy('name')->get(['id', 'name', 'department']);
    }

    /** List of active plants for visitors/kiosk to pick from. */
    public function plants()
    {
        return Plant::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'location']);
    }

    /**
     * Public list of today's common visitors for the mobile check-in app.
     * By default returns only pending visitors (who haven't submitted feedback yet today).
     */
    public function todayVisitors(Request $request)
    {
        $status = $request->query('status', 'pending'); // 'pending' or 'all'

        $query = Visit::with(['visitor', 'feedback', 'organizer'])
            ->whereDate('visit_date', today());

        if ($status === 'pending') {
            $query->doesntHave('feedback');
        }

        $visits = $query->latest('id')->get()->map(function ($v) {
            return [
                'id' => (int) $v->id,
                'visitor_id' => $v->visitor_id,
                'visitor_code' => $v->visitor_code,
                'name' => $v->visitor_name ?? $v->visitor?->name ?? 'Valued Visitor',
                'company' => $v->visitor_company ?? $v->visitor?->company ?? '',
                'designation' => $v->visitor_designation ?? $v->visitor?->designation ?? '',
                'mobile' => $v->visitor_mobile ?? $v->visitor?->mobile ?? '',
                'email' => $v->visitor_email ?? $v->visitor?->email ?? '',
                'purpose' => $v->purpose ?? 'Plant & Facility Tour',
                'organizer_id' => $v->organizer_id,
                'organizer_name' => $v->organizer?->name,
                'has_feedback' => !is_null($v->feedback),
                'visit_date' => $v->visit_date ? $v->visit_date->format('Y-m-d') : date('Y-m-d'),
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $visits->count(),
            'data' => $visits,
        ]);
    }

    /** Screen 3: feedback questions. */
    public function questions()
    {
        return Question::active()->get();
    }

    /**
     * Screen 4: one request = visitor details + selected organizer + answers.
     * Creates the visit and the feedback together (no orphan visits).
     */
    public function submitFeedback(Request $request)
    {
        $data = $request->validate([
            'visit_id' => 'nullable|integer|exists:visits,id',
            'visitor_code' => 'nullable|string|max:100',
            'plant_id' => 'required|integer|exists:plants,id',
            'organizer_id' => 'required|integer',
            'visitor_name' => ['required', 'string', 'min:2', 'max:100', 'regex:~^[\p{L}\s\.\-’\']+$~u'],
            'visitor_designation' => ['nullable', 'string', 'min:2', 'max:150', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'visitor_mobile' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'visitor_company' => ['nullable', 'string', 'min:2', 'max:150', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'visitor_email' => ['nullable', 'string', 'email:rfc', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', 'max:150'],
            'purpose' => ['nullable', 'string', 'min:2', 'max:255', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'overall_rating' => 'nullable|integer|between:1,5',
            'comments' => 'nullable|string|max:2000',
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer' => 'nullable|string|max:2000',
            'answers.*.reason' => 'nullable|string|max:2000',
        ], [
            'visitor_mobile.regex' => 'Mobile number must be a valid 10-digit number starting with 6, 7, 8, or 9.',
            'visitor_name.regex' => 'Visitor Name may only contain letters, spaces, hyphens, and dots.',
            'visitor_company.regex' => 'Company Name contains invalid characters.',
            'visitor_email.regex' => 'Please provide a valid email address with domain.',
            'visitor_designation.regex' => 'Visitor Designation contains invalid characters.',
            'purpose.regex' => 'Purpose contains invalid characters.',
        ]);

        $organizer = User::where('id', $data['organizer_id'])
            ->whereIn('role', ['organizer', 'staff'])->where('is_active', true)->firstOrFail();

        // all active + required questions must be answered
        $answered = collect($data['answers'])
            ->filter(fn ($a) => filled($a['answer'] ?? null))->pluck('question_id');
        $missing = Question::active()->where('is_required', true)->pluck('id')->diff($answered);

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages(['answers' => ['Please answer all required questions.']]);
        }

        // Duplicate Feedback Prevention (BRS Section 6.2 Requirement 4)
        $existingVisit = null;
        if (!empty($data['visit_id'])) {
            $existingVisit = Visit::with('feedback')->find($data['visit_id']);
        } elseif (!empty($data['visitor_code'])) {
            $existingVisit = Visit::with('feedback')->where('visitor_code', $data['visitor_code'])->whereDate('visit_date', today())->first();

            // Check if visitor has a past uncompleted visit that has expired
            if (! $existingVisit) {
                $pastUncompletedVisit = Visit::with('feedback')
                    ->where('visitor_code', $data['visitor_code'])
                    ->whereDate('visit_date', '<', today())
                    ->doesntHave('feedback')
                    ->latest('visit_date')
                    ->first();

                if ($pastUncompletedVisit && $pastUncompletedVisit->is_feedback_expired) {
                    abort(422, 'Feedback submission has expired for this visit. Feedback must be submitted on the day of the visit.');
                }
            }
        }

        if ($existingVisit && $existingVisit->feedback) {
            abort(422, 'Thank you! Your feedback has already been received for this visit.');
        }

        if ($existingVisit && $existingVisit->is_feedback_expired) {
            abort(422, 'Feedback submission has expired for this visit. Feedback must be submitted on the day of the visit.');
        }

        // basic double-submit guard: same mobile + same organizer + same day with existing feedback
        if (! empty($data['visitor_mobile']) && Visit::whereHas('visitor', fn ($q) => $q->where('mobile', $data['visitor_mobile']))
            ->where('organizer_id', $organizer->id)->whereDate('visit_date', today())->has('feedback')->exists()) {
            abort(422, 'Thank you! Your feedback has already been received for this visit.');
        }

        $feedback = DB::transaction(function () use ($data, $organizer, $existingVisit) {
            if ($existingVisit) {
                $visit = $existingVisit;
                $visit->update(array_filter([
                    'plant_id' => $data['plant_id'],
                    'organizer_id' => $organizer->id,
                    'purpose' => $data['purpose'] ?? $visit->purpose,
                ]));
                if ($visit->visitor) {
                    $visit->visitor->update(array_filter([
                        'designation' => $data['visitor_designation'] ?? $visit->visitor->designation,
                        'mobile' => $data['visitor_mobile'] ?? $visit->visitor->mobile,
                        'company' => $data['visitor_company'] ?? $visit->visitor->company,
                        'email' => $data['visitor_email'] ?? $visit->visitor->email,
                    ]));
                }
            } else {
                $visit = Visit::create([
                    'plant_id' => $data['plant_id'],
                    'organizer_id' => $organizer->id,
                    'visitor_name' => $data['visitor_name'],
                    'visitor_designation' => $data['visitor_designation'] ?? null,
                    'visitor_mobile' => $data['visitor_mobile'] ?? null,
                    'visitor_company' => $data['visitor_company'] ?? null,
                    'visitor_email' => $data['visitor_email'] ?? null,
                    'visit_date' => today(),
                    'purpose' => $data['purpose'] ?? null,
                ]);
            }

            $feedback = Feedback::create([
                'visit_id' => $visit->id,
                'plant_id' => $data['plant_id'],
                'organizer_id' => $organizer->id,
                'overall_rating' => $data['overall_rating'] ?? null,
                'comments' => $data['comments'] ?? null,
                'submitted_mode' => 'direct',
                'submitted_by_staff_id' => null,
                'submitted_at' => now(),
            ]);

            $feedback->answers()->createMany(
                collect($data['answers'])->map(fn ($a) => [
                    'question_id' => $a['question_id'],
                    'answer' => $a['answer'] ?? null,
                    'reason' => $a['reason'] ?? null,
                ])->all()
            );

            return $feedback;
        });

        return response()->json([
            'message' => 'Thank you for your feedback!',
            'feedback_id' => $feedback->id,
            'visitor_code' => $feedback->visit?->visitor_code,
        ], 201);
    }

    /**
     * Look up visitor details by their unique visitor ID / pass number.
     * Enables submitting feedback using Visitor ID without login.
     */
    public function lookup(string $visitor_id)
    {
        $visitor = \App\Models\Visitor::where('visitor_id', $visitor_id)
            ->orWhere('external_id', $visitor_id)
            ->first();

        if (! $visitor) {
            return response()->json([
                'success' => false,
                'message' => "Visitor with ID '{$visitor_id}' not found. Please check your pass or contact the front desk.",
            ], 404);
        }

        // Find today's visit or create one
        $visit = Visit::where(function ($q) use ($visitor) {
            $q->where('visitor_id', $visitor->id)->orWhere('visitor_code', $visitor->visitor_id);
        })->whereDate('visit_date', today())->with(['feedback', 'organizer:id,name,department'])->latest('id')->first();

        if (! $visit) {
            $visit = Visit::create([
                'visitor_id' => $visitor->id,
                'visitor_code' => $visitor->visitor_id,
                'visit_date' => today(),
                'purpose' => 'Plant & Facility Tour',
            ]);
        }

        return response()->json([
            'success' => true,
            'visitor' => [
                'id' => $visitor->id,
                'visitor_id' => $visitor->visitor_id,
                'name' => $visitor->name,
                'company' => $visitor->company,
                'designation' => $visitor->designation,
                'mobile' => $visitor->mobile,
                'email' => $visitor->email,
            ],
            'visit' => [
                'id' => $visit->id,
                'visitor_code' => $visit->visitor_code,
                'organizer_id' => $visit->organizer_id,
                'organizer_name' => $visit->organizer?->name,
                'has_feedback' => ! is_null($visit->feedback),
                'rating' => $visit->feedback?->overall_rating,
            ],
        ]);
    }
}
