<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
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
        }

        if ($existingVisit && $existingVisit->feedback) {
            abort(422, 'Thank you! Your feedback has already been received for this visit.');
        }

        // basic double-submit guard: same mobile + same organizer + same day with existing feedback
        if (! empty($data['visitor_mobile']) && Visit::where('visitor_mobile', $data['visitor_mobile'])
            ->where('organizer_id', $organizer->id)->whereDate('visit_date', today())->has('feedback')->exists()) {
            abort(422, 'Thank you! Your feedback has already been received for this visit.');
        }

        $feedback = DB::transaction(function () use ($data, $organizer, $existingVisit) {
            if ($existingVisit) {
                $visit = $existingVisit;
                $visit->update(array_filter([
                    'visitor_designation' => $data['visitor_designation'] ?? $visit->visitor_designation,
                    'visitor_mobile' => $data['visitor_mobile'] ?? $visit->visitor_mobile,
                    'visitor_company' => $data['visitor_company'] ?? $visit->visitor_company,
                    'visitor_email' => $data['visitor_email'] ?? $visit->visitor_email,
                    'purpose' => $data['purpose'] ?? $visit->purpose,
                ]));
            } else {
                $currentShift = \App\Models\Shift::current();
                $visit = Visit::create([
                    'organizer_id' => $organizer->id,
                    'visitor_name' => $data['visitor_name'],
                    'visitor_designation' => $data['visitor_designation'] ?? null,
                    'visitor_mobile' => $data['visitor_mobile'] ?? null,
                    'visitor_company' => $data['visitor_company'] ?? null,
                    'visitor_email' => $data['visitor_email'] ?? null,
                    'visit_date' => today(),
                    'shift_id' => $currentShift?->id,
                    'purpose' => $data['purpose'] ?? null,
                ]);
            }

            $feedback = Feedback::create([
                'visit_id' => $visit->id,
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

        // Find today's visit or create one under current shift
        $visit = Visit::where(function ($q) use ($visitor) {
            $q->where('visitor_id', $visitor->id)->orWhere('visitor_code', $visitor->visitor_id);
        })->whereDate('visit_date', today())->with(['feedback', 'shift', 'organizer:id,name,department'])->latest('id')->first();

        if (! $visit) {
            $currentShift = \App\Models\Shift::current();
            $visit = Visit::create([
                'visitor_id' => $visitor->id,
                'visitor_code' => $visitor->visitor_id,
                'visitor_name' => $visitor->name,
                'visitor_company' => $visitor->company,
                'visitor_designation' => $visitor->designation,
                'visitor_mobile' => $visitor->mobile,
                'visitor_email' => $visitor->email,
                'visit_date' => today(),
                'shift_id' => $currentShift?->id,
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
                'shift_name' => $visit->shift?->name,
                'organizer_id' => $visit->organizer_id,
                'organizer_name' => $visit->organizer?->name,
                'has_feedback' => ! is_null($visit->feedback),
                'rating' => $visit->feedback?->overall_rating,
            ],
        ]);
    }
}
