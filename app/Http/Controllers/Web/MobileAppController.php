<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Banner;
use App\Models\Company;
use App\Models\Feedback;
use App\Models\Plant;
use App\Models\Question;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;

class MobileAppController extends Controller
{
    /**
     * Unified Mobile & Tablet Application Screen
     * Serves both Visitor (no login needed) and Organizer (login required).
     */
    public function index(Request $request)
    {
        $plants = Plant::where('is_active', true)->orderBy('code')->get();
        $organizers = User::where('role', 'organizer')
            ->where('is_active', true)
            ->with('plant')
            ->orderBy('name')
            ->get();

        $questions = Question::active()->get();
        $sections = $questions->groupBy(fn ($q) => $q->section ?: 'General Questionnaire');

        $banners = Banner::active()
            ->forTarget('mobile')
            ->ordered()
            ->get();

        $company = Company::current();
        $authUser = Auth::user();
        $organizerStats = null;
        if ($authUser && in_array($authUser->role, ['organizer', 'admin', 'superadmin'], true)) {
            $organizerStats = [
                'total_visits' => Visit::where('organizer_id', $authUser->id)->count(),
                'today_visits' => Visit::where('organizer_id', $authUser->id)->whereDate('visit_date', today())->count(),
                'avg_rating' => round(Feedback::where('organizer_id', $authUser->id)->avg('overall_rating') ?? 5, 1),
            ];
        }

        return view('app.mobile', [
            'plants' => $plants,
            'organizers' => $organizers,
            'questions' => $questions,
            'sections' => $sections,
            'banners' => $banners,
            'company' => $company,
            'authUser' => $authUser,
            'organizerStats' => $organizerStats,
        ]);
    }

    /**
     * Submit visitor feedback (no authentication required).
     */
    public function submitFeedback(Request $request)
    {
        $validated = $request->validate([
            'organizer_id' => 'required|exists:users,id',
            'plant_id' => 'nullable|exists:plants,id',
            'visitor_name' => ['required', 'string', 'min:2', 'max:100', 'regex:~^[\p{L}\s\.\-’\']+$~u'],
            'visitor_company' => ['required', 'string', 'min:2', 'max:150', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'visitor_mobile' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'visitor_email' => ['nullable', 'string', 'email:rfc', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', 'max:100'],
            'visitor_designation' => ['nullable', 'string', 'min:2', 'max:100', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'purpose' => ['nullable', 'string', 'min:2', 'max:200', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'overall_rating' => 'nullable|integer|between:1,5',
            'comments' => ['nullable', 'string', 'max:2000'],
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer' => 'nullable',
        ], [
            'visitor_mobile.regex' => 'Mobile number must be a valid 10-digit number starting with 6, 7, 8, or 9.',
            'visitor_name.regex' => 'Visitor Name may only contain letters, spaces, hyphens, and dots.',
            'visitor_company.regex' => 'Company Name contains invalid characters.',
            'visitor_email.regex' => 'Please provide a valid email address with domain (e.g. name@domain.com).',
        ]);

        $organizer = User::findOrFail($validated['organizer_id']);

        // Check required questions
        $activeRequired = Question::active()->where('is_required', true)->pluck('id');
        $answered = collect($validated['answers'])
            ->filter(fn ($a) => !empty($a['answer']))
            ->pluck('question_id');
        
        $missing = $activeRequired->diff($answered);
        if ($missing->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Please answer all mandatory evaluation questions.',
                'missing_question_ids' => $missing->values(),
            ], 422);
        }

        $feedback = DB::transaction(function () use ($validated, $organizer) {
            $visit = Visit::create([
                'organizer_id' => $organizer->id,
                'visitor_name' => $validated['visitor_name'],
                'visitor_company' => $validated['visitor_company'],
                'visitor_mobile' => $validated['visitor_mobile'] ?? null,
                'visitor_email' => $validated['visitor_email'] ?? null,
                'visitor_designation' => $validated['visitor_designation'] ?? null,
                'visit_date' => today(),
                'purpose' => $validated['purpose'] ?? 'Plant & Technical Centre Tour',
            ]);

            $feedback = Feedback::create([
                'visit_id' => $visit->id,
                'organizer_id' => $organizer->id,
                'overall_rating' => $validated['overall_rating'] ?? 5,
                'comments' => $validated['comments'] ?? null,
                'submitted_at' => now(),
            ]);

            foreach ($validated['answers'] as $ans) {
                $answerValue = is_array($ans['answer'] ?? null) 
                    ? implode(', ', $ans['answer']) 
                    : ($ans['answer'] ?? null);

                $feedback->answers()->create([
                    'question_id' => $ans['question_id'],
                    'answer' => $answerValue,
                ]);
            }

            AuditLog::record(
                'create',
                'feedbacks',
                "New visitor evaluation submitted by {$visit->visitor_name} ({$visit->visitor_company}) with {$feedback->overall_rating} stars",
                ['visit_id' => $visit->id, 'rating' => $feedback->overall_rating]
            );

            return $feedback;
        });

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your feedback has been successfully submitted to Shibaura Machine.',
            'feedback_id' => $feedback->id,
        ]);
    }

    /**
     * Organizer login endpoint (requires credentials).
     */
    public function organizerLogin(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        $user = User::where($field, $request->login)
            ->where('is_active', true)
            ->whereIn('role', ['organizer', 'admin', 'superadmin'])
            ->with(['plant', 'roleModel'])
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials or inactive organizer account.',
            ], 401);
        }

        Auth::login($user);

        AuditLog::record('login', 'auth', "Organizer {$user->name} logged in via mobile application.", null, $user);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'role' => $user->roleModel?->name ?? ucfirst($user->role),
                'plant_id' => $user->plant_id,
                'plant_name' => $user->plant?->name ?? 'HQ / General',
                'plant_code' => $user->plant?->code ?? 'HQ',
                'department' => $user->department,
            ],
            'stats' => [
                'total_visits' => Visit::where('organizer_id', $user->id)->count(),
                'today_visits' => Visit::where('organizer_id', $user->id)->whereDate('visit_date', today())->count(),
                'avg_rating' => round(Feedback::where('organizer_id', $user->id)->avg('overall_rating') ?? 5, 1),
            ],
        ]);
    }

    /**
     * Organizer recent visits and reviews.
     */
    public function organizerVisits(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $visits = Visit::where('organizer_id', $user->id)
            ->with(['feedback.answers.question'])
            ->latest('visit_date')
            ->latest('id')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'visits' => $visits,
        ]);
    }

    /**
     * Organizer logout.
     */
    public function organizerLogout(Request $request)
    {
        if (Auth::check()) {
            AuditLog::record('logout', 'auth', 'Organizer logged out from mobile application.');
            Auth::logout();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Organizer forgot password request.
     */
    public function organizerForgotPassword(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
        ]);

        $login = trim($request->login);
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        $user = User::where($field, $login)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'No organizer account found for that email or mobile number.',
            ], 404);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is deactivated. Please contact admin.',
            ], 403);
        }

        $token = Password::createToken($user);
        $user->sendPasswordResetNotification($token);

        AuditLog::record('request', 'auth', "Password reset requested via mobile view for {$user->email}.", null, $user);

        return response()->json([
            'success' => true,
            'message' => "Password reset instructions dispatched to {$user->email}.",
            'email' => $user->email,
            'reset_url' => route('password.reset', ['token' => $token, 'email' => $user->email]),
        ]);
    }

    /**
     * Organizer change password while logged in.
     */
    public function organizerChangePassword(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'password.different' => 'The new password must be different from current password.',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect current password.',
            ], 422);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => \Illuminate\Support\Str::random(60),
        ])->save();

        AuditLog::record('update', 'auth', "Organizer {$user->name} changed password via mobile portal.", null, $user);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully!',
        ]);
    }
}

