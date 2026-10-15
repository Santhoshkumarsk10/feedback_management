<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Feedback;
use App\Models\Question;
use App\Models\User;
use App\Models\Visit;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'organizer_id' => 'nullable|integer|exists:users,id',
            'q' => ['nullable', 'string', 'max:100', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
        ]);

        $tab = $request->input('tab', 'all');
        $isTodayFilter = $request->boolean('today') || $request->input('date_filter') === 'today';

        $query = Visit::with(['organizer', 'feedback'])
            ->when($isTodayFilter, fn ($q) => $q->whereDate('visit_date', today()))
            ->when(! $isTodayFilter && $request->from, fn ($q, $v) => $q->whereDate('visit_date', '>=', $v))
            ->when(! $isTodayFilter && $request->to, fn ($q, $v) => $q->whereDate('visit_date', '<=', $v))
            ->when($request->organizer_id, fn ($q, $v) => $q->where('organizer_id', $v))
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('visitor_code', 'like', "%$v%")
                ->orWhereHas('visitor', fn ($vq) => $vq
                    ->where('name', 'like', "%$v%")
                    ->orWhere('mobile', 'like', "%$v%")
                    ->orWhere('company', 'like', "%$v%"))))
            ->when($tab === 'completed', fn ($q) => $q->has('feedback'))
            ->when($tab === 'pending', fn ($q) => $q->doesntHave('feedback'));

        $baseCount = Visit::when($isTodayFilter, fn ($q) => $q->whereDate('visit_date', today()))
            ->when(! $isTodayFilter && $request->from, fn ($q, $v) => $q->whereDate('visit_date', '>=', $v))
            ->when(! $isTodayFilter && $request->to, fn ($q, $v) => $q->whereDate('visit_date', '<=', $v))
            ->when($request->organizer_id, fn ($q, $v) => $q->where('organizer_id', $v))
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('visitor_code', 'like', "%$v%")
                ->orWhereHas('visitor', fn ($vq) => $vq
                    ->where('name', 'like', "%$v%")
                    ->orWhere('mobile', 'like', "%$v%")
                    ->orWhere('company', 'like', "%$v%"))));

        $totalCount = (clone $baseCount)->count();
        $completedCount = (clone $baseCount)->has('feedback')->count();
        $pendingCount = (clone $baseCount)->doesntHave('feedback')->count();

        $visits = $query->latest('visit_date')->latest('id')->paginate(10)->withQueryString();

        $visitSuggestions = Visitor::where('is_active', true)
            ->latest('id')
            ->take(30)
            ->get()
            ->map(function ($v) {
                return [
                    'code' => $v->company ? substr($v->company, 0, 8) : null,
                    'name' => $v->name,
                    'sub' => ($v->company ? $v->company . ' • ' : '') . $v->mobile,
                    'value' => $v->name,
                ];
            });

        return view('visits.index', [
            'visits' => $visits,
            'organizers' => User::whereIn('role', ['organizer', 'staff'])->orderBy('name')->get(['id', 'name']),
            'tabCounts' => [
                'all' => $totalCount,
                'completed' => $completedCount,
                'pending' => $pendingCount,
            ],
            'currentTab' => $tab,
            'isTodayFilter' => $isTodayFilter,
            'visitSuggestions' => $visitSuggestions,
        ]);
    }

    public function create()
    {
        $organizers = User::whereIn('role', ['staff', 'organizer'])->orderBy('name')->get(['id', 'name']);

        return view('visits.create', compact('organizers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'visitor_name' => ['required', 'string', 'min:2', 'max:100', 'regex:~^[\p{L}\s\.\-’\']+$~u'],
            'visitor_company' => ['required', 'string', 'min:2', 'max:150', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'visitor_mobile' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'visitor_email' => ['nullable', 'string', 'email:rfc', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', 'max:150'],
            'visitor_designation' => ['nullable', 'string', 'min:2', 'max:150', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'purpose' => ['nullable', 'string', 'min:2', 'max:255', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'organizer_id' => 'nullable|integer|exists:users,id',
            'visit_date' => 'nullable|date',
        ], [
            'visitor_name.required' => 'Visitor full name is required.',
            'visitor_name.min' => 'Visitor full name must be at least 2 characters.',
            'visitor_name.max' => 'Visitor full name cannot exceed 100 characters.',
            'visitor_name.regex' => 'Visitor Name may only contain letters, spaces, hyphens, and dots.',
            'visitor_company.required' => 'Company or organization name is required.',
            'visitor_company.min' => 'Company name must be at least 2 characters.',
            'visitor_company.max' => 'Company name cannot exceed 150 characters.',
            'visitor_company.regex' => 'Company Name contains invalid characters.',
            'visitor_mobile.regex' => 'Mobile number must be a valid 10-digit number starting with 6, 7, 8, or 9.',
            'visitor_email.email' => 'Please provide a valid email address.',
            'visitor_email.regex' => 'Please provide a valid email address with a recognized domain (e.g. name@domain.com).',
            'visitor_email.max' => 'Email address cannot exceed 150 characters.',
            'visitor_designation.max' => 'Designation cannot exceed 150 characters.',
            'visitor_designation.regex' => 'Designation contains invalid characters.',
            'purpose.max' => 'Purpose cannot exceed 255 characters.',
            'purpose.regex' => 'Purpose contains invalid characters.',
            'visit_date.date' => 'Please provide a valid visit date.',
        ]);

        $organizerId = $request->organizer_id ?: auth()->id();
        if (! User::where('id', $organizerId)->exists()) {
            $organizerId = User::whereIn('role', ['staff', 'organizer'])->value('id');
        }

        $visit = Visit::create([
            'visitor_name' => $request->visitor_name,
            'visitor_company' => $request->visitor_company,
            'visitor_mobile' => $request->visitor_mobile,
            'visitor_email' => $request->visitor_email,
            'visitor_designation' => $request->visitor_designation,
            'purpose' => $request->purpose ?: 'Plant Tour & Technical Centre Visit',
            'organizer_id' => $organizerId,
            'visit_date' => $request->visit_date ?: today(),
        ]);

        AuditLog::record('create', 'visits', "Staff " . auth()->user()->name . " registered visitor {$visit->visitor_name} ({$visit->visitor_company})", [
            'visit_id' => $visit->id,
        ]);

        return redirect()->route('dashboard')->with('success', "Visitor {$visit->visitor_name} ({$visit->visitor_company}) successfully registered!");
    }

    public function createFeedback(Visit $visit)
    {
        if ($visit->feedback) {
            return redirect()->route('feedbacks.show', $visit->feedback)
                ->with('info', 'Feedback has already been recorded for this visit.');
        }

        if ($visit->is_feedback_expired) {
            $formattedDate = $visit->visit_date ? $visit->visit_date->format('d/m/Y') : 'the visit date';
            return redirect()->route('shift_queue')
                ->with('error', "Feedback collection for this visitor has expired. Feedback must be submitted on the day of the visit ({$formattedDate}).");
        }

        $visit->load(['organizer']);
        $questions = Question::active()->get();
        $sections = $questions->groupBy(fn ($q) => $q->section ?: 'General Questionnaire');

        return view('visits.submit-feedback', compact('visit', 'questions', 'sections'));
    }

    public function storeFeedback(Request $request, Visit $visit)
    {
        if ($visit->feedback) {
            return redirect()->route('feedbacks.show', $visit->feedback)
                ->with('info', 'Feedback has already been recorded for this visit.');
        }

        if ($visit->is_feedback_expired) {
            $formattedDate = $visit->visit_date ? $visit->visit_date->format('d/m/Y') : 'the visit date';
            return redirect()->route('shift_queue')
                ->with('error', "Feedback collection for this visitor has expired. Feedback must be submitted on the day of the visit ({$formattedDate}).");
        }

        $request->validate([
            'overall_rating' => 'required|integer|between:1,5',
            'comments' => 'nullable|string|max:2000',
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer' => 'nullable',
        ]);

        $activeRequired = Question::active()->where('is_required', true)->pluck('id');
        $answered = collect($request->answers)
            ->filter(fn ($a) => !empty($a['answer']))
            ->pluck('question_id');

        $missing = $activeRequired->diff($answered);
        if ($missing->isNotEmpty()) {
            return back()->withInput()->withErrors(['answers' => 'Please provide rating evaluations for all mandatory criteria.']);
        }

        $staffUser = auth()->user();

        $feedback = DB::transaction(function () use ($request, $visit, $staffUser) {
            $feedback = Feedback::create([
                'visit_id' => $visit->id,
                'organizer_id' => $visit->organizer_id ?: $staffUser->id,
                'overall_rating' => $request->overall_rating,
                'comments' => $request->comments,
                'submitted_mode' => 'staff_assisted',
                'submitted_by_staff_id' => $staffUser->id,
                'submitted_at' => now(),
            ]);

            foreach ($request->answers as $ans) {
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
                "Staff {$staffUser->name} submitted evaluation on behalf of visitor {$visit->visitor_name} ({$visit->visitor_company}) with {$feedback->overall_rating} stars",
                ['visit_id' => $visit->id, 'rating' => $feedback->overall_rating]
            );

            return $feedback;
        });

        return redirect()->route('dashboard')->with('success', "Feedback successfully submitted on behalf of {$visit->visitor_name} ({$visit->visitor_company})!");
    }

    /**
     * Trigger synchronization with external 3rd-party visitor API.
     */
    public function syncExternal(Request $request, \App\Services\ExternalVisitorSyncService $syncService)
    {
        $result = $syncService->syncVisitors($request->input('date'), auth()->id());

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', $result['message']);
    }
}
