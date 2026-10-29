<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Feedback;
use App\Models\Plant;
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
            'per_page' => 'nullable|integer',
            'organizer_id' => 'nullable|integer|exists:users,id',
            'q' => ['nullable', 'string', 'max:100', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
        ]);

        $tab = $request->input('tab', 'all');
        $isTodayFilter = $request->boolean('today') || $request->input('date_filter') === 'today';
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $query = Visit::with(['organizer', 'feedback'])
            ->when($isTodayFilter, fn ($q) => $q->whereDate('visit_date', today()))
            ->when(! $isTodayFilter && $request->filled('from'), fn ($q) => $q->whereDate('visit_date', '>=', $request->input('from')))
            ->when(! $isTodayFilter && $request->filled('to'), fn ($q) => $q->whereDate('visit_date', '<=', $request->input('to')))
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
            ->when(! $isTodayFilter && $request->filled('from'), fn ($q) => $q->whereDate('visit_date', '>=', $request->input('from')))
            ->when(! $isTodayFilter && $request->filled('to'), fn ($q) => $q->whereDate('visit_date', '<=', $request->input('to')))
            ->when($request->organizer_id, fn ($q, $v) => $q->where('organizer_id', $v))
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('visitor_code', 'like', "%$v%")
                ->orWhereHas('visitor', fn ($vq) => $vq
                    ->where('name', 'like', "%$v%")
                    ->orWhere('mobile', 'like', "%$v%")
                    ->orWhere('company', 'like', "%$v%"))));

        $totalCount = min((clone $baseCount)->count(), 1000);
        $completedCount = min((clone $baseCount)->has('feedback')->count(), 1000);
        $pendingCount = min((clone $baseCount)->doesntHave('feedback')->count(), 1000);

        $visits = $query->latest('visit_date')->latest('id')->paginateCapped($perPage, 1000);

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
            'organizers' => User::where('is_active', true)->orderBy('name')->get(['id', 'name']),
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
        $organizers = User::where('is_active', true)->orderBy('name')->get(['id', 'name', 'role', 'department']);

        return view('visits.create', compact('organizers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'visitor_name' => ['required', 'string', 'min:2', 'max:100', 'regex:~^[\p{L}\s]+$~u'],
            'visitor_company' => ['required', 'string', 'min:2', 'max:150', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'visitor_mobile' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'visitor_email' => ['nullable', 'string', 'regex:/^[a-zA-Z0-9]+([._-][a-zA-Z0-9]+)*@[a-zA-Z0-9]+([.-][a-zA-Z0-9]+)*\.[a-zA-Z]{2,}$/', 'max:150'],
            'visitor_designation' => ['nullable', 'string', 'min:2', 'max:150', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u', 'not_regex:/^[0-9]+$/'],
            'purpose' => ['nullable', 'string', 'min:2', 'max:150', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u', 'not_regex:/^[0-9]+$/'],
            'organizer_id' => 'nullable|integer|exists:users,id',
            'visit_date' => 'nullable|date',
        ], [
            'visitor_name.required' => 'Visitor full name is required.',
            'visitor_name.min' => 'Visitor full name must be at least 2 characters.',
            'visitor_name.max' => 'Visitor full name cannot exceed 100 characters.',
            'visitor_name.regex' => 'Visitor Name may only contain letters and spaces. Special characters are not allowed.',
            'visitor_company.required' => 'Company or organization name is required.',
            'visitor_company.min' => 'Company name must be at least 2 characters.',
            'visitor_company.max' => 'Company name cannot exceed 150 characters.',
            'visitor_company.regex' => 'Company Name contains invalid characters.',
            'visitor_mobile.regex' => 'Mobile number must be a valid 10-digit number starting with 6, 7, 8, or 9.',
            'visitor_email.email' => 'Please provide a valid email format (e.g. user@gmail.com, john.doe@yahoo.com, user123@outlook.com, john_smith@example.co.in).',
            'visitor_email.regex' => 'Please provide a valid email format (e.g. user@gmail.com, john.doe@yahoo.com, user123@outlook.com, john_smith@example.co.in).',
            'visitor_email.max' => 'Email address cannot exceed 150 characters.',
            'visitor_designation.max' => 'Designation cannot exceed 150 characters.',
            'visitor_designation.regex' => 'Designation contains invalid characters.',
            'visitor_designation.not_regex' => 'Designation cannot be numbers only.',
            'purpose.max' => 'Purpose cannot exceed 150 characters.',
            'purpose.regex' => 'Purpose contains invalid characters.',
            'purpose.not_regex' => 'Purpose cannot be numbers only.',
            'visit_date.date' => 'Please provide a valid visit date.',
        ]);

        $organizerId = $request->organizer_id ?: auth()->id();
        if (! User::where('id', $organizerId)->where('is_active', true)->exists()) {
            $organizerId = User::where('is_active', true)->value('id');
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

    public function show(Visit $visit)
    {
        $visit->load(['organizer', 'feedback']);

        if ($visit->feedback) {
            return redirect()->route('feedbacks.show', $visit->feedback);
        }

        if (! $visit->is_feedback_expired) {
            return redirect()->route('visits.feedback.create', $visit);
        }

        return redirect()->route('visits.index', ['q' => $visit->visitor_code ?: $visit->id]);
    }

    public function createFeedback(Visit $visit)
    {
        if ($visit->feedback) {
            return redirect()->route('feedbacks.show', $visit->feedback)
                ->with('info', 'Feedback has already been recorded for this visit.');
        }

        if ($visit->is_feedback_expired) {
            $formattedDate = $visit->visit_date ? $visit->visit_date->format('d/m/Y') : 'the visit date';
            $target = auth()->check() ? 'shift_queue' : 'visitor.home';
            return redirect()->route($target)
                ->with('error', "Feedback collection for this visitor has expired. Feedback must be submitted on the day of the visit ({$formattedDate}).");
        }

        $visit->load(['organizer', 'plant']);
        $plants = Plant::where('is_active', true)->orderBy('name')->get();
        $organizers = User::where('is_active', true)->orderBy('name')->get();
        $questions = Question::active()->get();
        $sections = $questions->groupBy(fn ($q) => $q->section ?: 'General Questionnaire');

        return view('visits.submit-feedback', compact('visit', 'questions', 'sections', 'plants', 'organizers'));
    }

    public function storeFeedback(Request $request, Visit $visit)
    {
        if ($visit->feedback) {
            if (auth()->check()) {
                return redirect()->route('feedbacks.show', $visit->feedback)
                    ->with('info', 'Feedback has already been recorded for this visit.');
            }
            return redirect()->route('visitor.home')
                ->with('info', 'Feedback has already been recorded for this visit.');
        }

        if ($visit->is_feedback_expired) {
            $formattedDate = $visit->visit_date ? $visit->visit_date->format('d/m/Y') : 'the visit date';
            $target = auth()->check() ? 'shift_queue' : 'visitor.home';
            return redirect()->route($target)
                ->with('error', "Feedback collection for this visitor has expired. Feedback must be submitted on the day of the visit ({$formattedDate}).");
        }

        // 1. Resolve and normalize plant_id when provided (supports ID, Code, Name, or full label)
        $rawPlant = $request->input('plant_id')
            ?? $request->input('plant')
            ?? $request->input('facility')
            ?? $request->input('facility_id')
            ?? $request->input('plant_code');

        if (!empty($rawPlant)) {
            $resolvedPlantId = null;
            if (is_numeric($rawPlant) && Plant::where('id', (int) $rawPlant)->exists()) {
                $resolvedPlantId = (int) $rawPlant;
            } else {
                $plantStr = trim((string) $rawPlant);
                $plant = Plant::where('code', $plantStr)->first()
                    ?? Plant::where('name', $plantStr)->first();

                if (!$plant && preg_match('/PLANT-\d+/i', $plantStr, $m)) {
                    $plant = Plant::where('code', strtoupper($m[0]))->first();
                }

                if (!$plant) {
                    $plant = Plant::where('name', 'like', "%{$plantStr}%")->first();
                }

                if ($plant) {
                    $resolvedPlantId = $plant->id;
                }
            }

            if ($resolvedPlantId) {
                $request->merge(['plant_id' => $resolvedPlantId]);
            }
        }

        // 2. Resolve and normalize organizer_id when provided (supports ID, Name, Email)
        $rawOrganizer = $request->input('organizer_id')
            ?? $request->input('organizer')
            ?? $request->input('staff_id');

        if (!empty($rawOrganizer)) {
            $resolvedOrganizerId = null;
            if (is_numeric($rawOrganizer) && User::where('id', (int) $rawOrganizer)->exists()) {
                $resolvedOrganizerId = (int) $rawOrganizer;
            } else {
                $orgStr = trim((string) $rawOrganizer);
                $user = User::where('name', $orgStr)->first()
                    ?? User::where('email', $orgStr)->first()
                    ?? User::where('name', 'like', "%{$orgStr}%")->first();
                if ($user) {
                    $resolvedOrganizerId = $user->id;
                }
            }

            if ($resolvedOrganizerId) {
                $request->merge(['organizer_id' => $resolvedOrganizerId]);
            }
        }

        $request->validate([
            'plant_id' => 'required|integer|exists:plants,id',
            'organizer_id' => 'required|integer|exists:users,id',
            'overall_rating' => 'required|integer|between:1,5',
            'comments' => ['nullable', 'string', 'max:2000', 'regex:~^[\p{L}\p{N}\s\.\,\-\–\—\_\&\/\(\)\'\"\!\?\:\;\%\r\n]+$~u'],
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer' => ['nullable', function ($attribute, $value, $fail) {
                if (is_string($value) && mb_strlen($value) > 1000) {
                    $fail("The answer cannot exceed 1000 characters.");
                }
                if (is_string($value) && !empty($value) && !preg_match('~^[\p{L}\p{N}\s\.\,\-\–\—\_\&\/\(\)\'\"\!\?\:\;\%\r\n]+$~u', $value)) {
                    $fail("The answer contains unsupported special characters.");
                }
                if (is_array($value) && mb_strlen(implode(', ', $value)) > 1000) {
                    $fail("The answer cannot exceed 1000 characters.");
                }
            }],
        ], [
            'plant_id.required' => 'Please select the manufacturing plant facility.',
            'plant_id.exists' => 'Selected manufacturing plant facility is invalid.',
            'organizer_id.required' => 'Please select the host tour organizer.',
            'organizer_id.exists' => 'Selected tour organizer is invalid.',
            'comments.regex' => 'Visitor comments contain unsupported special characters.',
        ]);

        $activeRequired = Question::active()->where('is_required', true)->pluck('id');
        $answered = collect($request->answers)
            ->filter(fn ($a) => !empty($a['answer']))
            ->pluck('question_id');

        $missing = $activeRequired->diff($answered);
        if ($missing->isNotEmpty()) {
            return back()->withInput()->withErrors(['answers' => 'Please provide rating evaluations for all mandatory criteria.']);
        }

        $staffUser = auth()->user() ?: User::where('id', $request->organizer_id)->first() ?: User::first();
        $isStaffAssist = auth()->check();

        $feedback = DB::transaction(function () use ($request, $visit, $staffUser, $isStaffAssist) {
            $feedback = Feedback::create([
                'visit_id' => $visit->id,
                'plant_id' => $request->plant_id,
                'organizer_id' => $request->organizer_id,
                'overall_rating' => $request->overall_rating,
                'comments' => $request->comments,
                'submitted_mode' => $isStaffAssist ? 'staff_assisted' : 'direct',
                'submitted_by_staff_id' => $isStaffAssist ? $staffUser->id : null,
                'submitted_at' => now(),
            ]);

            $visit->update([
                'plant_id' => $request->plant_id,
                'organizer_id' => $request->organizer_id,
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

            $submitterName = $isStaffAssist ? ("Staff " . $staffUser->name) : "Visitor directly";
            AuditLog::record(
                'create',
                'feedbacks',
                "{$submitterName} submitted evaluation on behalf of visitor {$visit->visitor_name} ({$visit->visitor_company}) with {$feedback->overall_rating} stars",
                ['visit_id' => $visit->id, 'rating' => $feedback->overall_rating]
            );

            return $feedback;
        });

        if (auth()->check()) {
            return redirect()->route('dashboard')->with('success', "Feedback successfully submitted on behalf of {$visit->visitor_name} ({$visit->visitor_company})!");
        }

        return redirect()->route('visitor.home')->with('success', "Thank you! Your feedback for {$visit->visitor_name} ({$visit->visitor_company}) has been successfully submitted.");
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
