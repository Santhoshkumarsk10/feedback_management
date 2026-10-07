<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Feedback;
use App\Models\Question;
use App\Models\Shift;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'shift_id' => 'nullable|integer|exists:shifts,id',
            'organizer_id' => 'nullable|integer|exists:users,id',
            'q' => ['nullable', 'string', 'max:100', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
        ]);

        $tab = $request->input('tab', 'all');
        $isTodayFilter = $request->boolean('today') || $request->input('date_filter') === 'today';

        $query = Visit::with(['organizer', 'feedback', 'shift'])
            ->when($isTodayFilter, fn ($q) => $q->whereDate('visit_date', today()))
            ->when(! $isTodayFilter && $request->from, fn ($q, $v) => $q->whereDate('visit_date', '>=', $v))
            ->when(! $isTodayFilter && $request->to, fn ($q, $v) => $q->whereDate('visit_date', '<=', $v))
            ->when($request->shift_id, fn ($q, $v) => $q->where('shift_id', $v))
            ->when($request->organizer_id, fn ($q, $v) => $q->where('organizer_id', $v))
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('visitor_name', 'like', "%$v%")
                ->orWhere('visitor_mobile', 'like', "%$v%")
                ->orWhere('visitor_company', 'like', "%$v%")
                ->orWhere('visitor_code', 'like', "%$v%")))
            ->when($tab === 'completed', fn ($q) => $q->has('feedback'))
            ->when($tab === 'pending', fn ($q) => $q->doesntHave('feedback'));

        $baseCount = Visit::when($isTodayFilter, fn ($q) => $q->whereDate('visit_date', today()))
            ->when(! $isTodayFilter && $request->from, fn ($q, $v) => $q->whereDate('visit_date', '>=', $v))
            ->when(! $isTodayFilter && $request->to, fn ($q, $v) => $q->whereDate('visit_date', '<=', $v))
            ->when($request->shift_id, fn ($q, $v) => $q->where('shift_id', $v))
            ->when($request->organizer_id, fn ($q, $v) => $q->where('organizer_id', $v))
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('visitor_name', 'like', "%$v%")
                ->orWhere('visitor_mobile', 'like', "%$v%")
                ->orWhere('visitor_company', 'like', "%$v%")
                ->orWhere('visitor_code', 'like', "%$v%")));

        $totalCount = (clone $baseCount)->count();
        $completedCount = (clone $baseCount)->has('feedback')->count();
        $pendingCount = (clone $baseCount)->doesntHave('feedback')->count();

        $visits = $query->latest('visit_date')->latest('id')->paginate(10)->withQueryString();

        $visitSuggestions = Visit::select('id', 'visitor_name', 'visitor_company', 'visitor_mobile')
            ->latest('id')
            ->take(30)
            ->get()
            ->map(function ($v) {
                return [
                    'code' => $v->visitor_company ? substr($v->visitor_company, 0, 8) : null,
                    'name' => $v->visitor_name,
                    'sub' => ($v->visitor_company ? $v->visitor_company . ' • ' : '') . $v->visitor_mobile,
                    'value' => $v->visitor_name,
                ];
            });

        return view('visits.index', [
            'visits' => $visits,
            'shifts' => Shift::active()->orderBy('start_time')->get(),
            'currentShift' => Shift::current(),
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
        $shifts = Shift::active()->orderBy('start_time')->get();
        $currentShift = Shift::current();
        $organizers = User::whereIn('role', ['staff', 'organizer'])->orderBy('name')->get(['id', 'name']);

        return view('visits.create', compact('shifts', 'currentShift', 'organizers'));
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
            'shift_id' => 'required|integer|exists:shifts,id',
            'organizer_id' => 'nullable|integer|exists:users,id',
            'visit_date' => 'nullable|date',
        ], [
            'visitor_mobile.regex' => 'Mobile number must be a valid 10-digit number starting with 6, 7, 8, or 9.',
            'visitor_name.regex' => 'Visitor Name may only contain letters, spaces, hyphens, and dots.',
            'visitor_company.regex' => 'Company Name contains invalid characters.',
            'visitor_email.regex' => 'Please provide a valid email address with domain.',
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
            'shift_id' => $request->shift_id,
            'organizer_id' => $organizerId,
            'visit_date' => $request->visit_date ?: today(),
        ]);

        AuditLog::record('create', 'visits', "Staff " . auth()->user()->name . " registered visitor {$visit->visitor_name} ({$visit->visitor_company}) for " . ($visit->shift?->name ?? 'Shift'), [
            'visit_id' => $visit->id,
            'shift_id' => $visit->shift_id,
        ]);

        return redirect()->route('dashboard')->with('success', "Visitor {$visit->visitor_name} ({$visit->visitor_company}) successfully registered for {$visit->shift?->name}!");
    }

    public function createFeedback(Visit $visit)
    {
        if ($visit->feedback) {
            return redirect()->route('feedbacks.show', $visit->feedback)
                ->with('info', 'Feedback has already been recorded for this visit.');
        }

        $visit->load(['organizer', 'shift']);
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
                'organizer_id' => $staffUser->id ?: $visit->organizer_id,
                'overall_rating' => $request->overall_rating,
                'comments' => $request->comments,
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
