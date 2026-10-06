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
        return User::where('role', 'organizer')->where('is_active', true)
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
            'organizer_id' => 'required|integer',
            'visitor_name' => 'required|string|max:100',
            'visitor_designation' => 'nullable|string|max:150',
            'visitor_mobile' => 'nullable|digits_between:10,15',
            'visitor_company' => 'nullable|string|max:150',
            'visitor_email' => 'nullable|email|max:150',
            'purpose' => 'nullable|string|max:255',
            'overall_rating' => 'nullable|integer|between:1,5',
            'comments' => 'nullable|string|max:2000',
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer' => 'nullable|string|max:2000',
        ]);

        $organizer = User::where('id', $data['organizer_id'])
            ->where('role', 'organizer')->where('is_active', true)->firstOrFail();

        // all active + required questions must be answered
        $answered = collect($data['answers'])
            ->filter(fn ($a) => filled($a['answer'] ?? null))->pluck('question_id');
        $missing = Question::active()->where('is_required', true)->pluck('id')->diff($answered);

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages(['answers' => ['Please answer all required questions.']]);
        }

        // basic double-submit guard: same mobile + same organizer + same day
        if (! empty($data['visitor_mobile']) && Visit::where('visitor_mobile', $data['visitor_mobile'])
            ->where('organizer_id', $organizer->id)->whereDate('visit_date', today())->exists()) {
            abort(422, 'Feedback already submitted today for this organizer.');
        }

        $feedback = DB::transaction(function () use ($data, $organizer) {
            $visit = Visit::create([
                'organizer_id' => $organizer->id,
                'visitor_name' => $data['visitor_name'],
                'visitor_designation' => $data['visitor_designation'] ?? null,
                'visitor_mobile' => $data['visitor_mobile'] ?? null,
                'visitor_company' => $data['visitor_company'] ?? null,
                'visitor_email' => $data['visitor_email'] ?? null,
                'visit_date' => today(),
                'purpose' => $data['purpose'] ?? null,
            ]);

            $feedback = Feedback::create([
                'visit_id' => $visit->id,
                'organizer_id' => $organizer->id,
                'overall_rating' => $data['overall_rating'] ?? null,
                'comments' => $data['comments'] ?? null,
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

        return response()->json(['message' => 'Thank you for your feedback!', 'feedback_id' => $feedback->id], 201);
    }
}
