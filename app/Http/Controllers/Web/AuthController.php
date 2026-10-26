<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:~^[a-zA-Z0-9@._+\-]+$~',
                function ($attribute, $value, $fail) {
                    if (str_contains($value, '@') && !preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $value)) {
                        $fail('Please provide a valid email address with a valid domain (e.g. name@company.com).');
                    }
                },
            ],
            'password' => ['required', 'string', 'max:100'],
        ], [
            'login.regex' => 'Login credential contains invalid characters.',
        ]);

        $field = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        if (Auth::attempt([$field => $data['login'], 'password' => $data['password'], 'is_active' => true], $request->boolean('remember'))) {
            $user = Auth::user();

            $isWebAuthorized = false;
            try {
                if ($user->hasAnyRole(['superadmin', 'admin', 'coordinator', 'supervisor', 'staff', 'organizer']) || $user->can('access-web-panel')) {
                    $isWebAuthorized = true;
                }
            } catch (\Throwable $e) {}

            if (!$isWebAuthorized && in_array($user->role, ['superadmin', 'admin', 'coordinator', 'supervisor', 'staff', 'organizer'], true)) {
                $isWebAuthorized = true;
            }

            if ($isWebAuthorized) {
                $request->session()->regenerate();
                AuditLog::record('login', 'auth', "User {$user->name} ({$user->role}) logged into operations web panel.", [
                    'login_field' => $field,
                    'role' => $user->role,
                    'ip' => $request->ip(),
                ]);

                return redirect()->intended(route('dashboard'));
            }

            AuditLog::record('denied', 'auth', 'Login attempt denied: account not authorized for web panel.', [
                'user' => $user->email,
                'role' => $user->role,
            ]);
            Auth::logout();
        }

        return back()->withErrors(['login' => 'Invalid credentials, or this account has no access.'])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLog::record('logout', 'auth', 'User logged out of active session.');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'login' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:~^[a-zA-Z0-9@._+\-]+$~',
                function ($attribute, $value, $fail) {
                    if (str_contains($value, '@') && !preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $value)) {
                        $fail('Please provide a valid email address with a valid domain (e.g. name@company.com).');
                    }
                },
            ],
        ], [
            'login.regex' => 'Login credential contains invalid characters.',
        ]);

        $login = trim($request->login);
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        $user = User::where($field, $login)->first();

        if (! $user) {
            return back()->withErrors(['login' => 'No active user found with that email or mobile number.'])->onlyInput('login');
        }

        if (! $user->is_active) {
            return back()->withErrors(['login' => 'This account is deactivated. Please contact your system administrator.'])->onlyInput('login');
        }

        // Generate token and send reset notification
        $token = Password::createToken($user);
        $user->sendPasswordResetNotification($token);

        AuditLog::record('request', 'auth', "Password reset requested for {$user->email}.", [
            'user_id' => $user->id,
            'ip' => $request->ip(),
        ], $user);

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
        $devLink = config('mail.default') === 'log' || app()->isLocal() ? $resetUrl : null;

        return back()->with([
            'status' => 'Password reset link has been dispatched to ' . $user->email . '.',
            'dev_reset_url' => $devLink,
            'reset_email' => $user->email,
        ]);
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => ['required', 'string', 'email:rfc', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', 'max:100'],
            'password' => 'required|string|min:8|max:100|confirmed',
        ], [
            'email.regex' => 'Please provide a valid email address with domain.',
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                AuditLog::record('update', 'auth', "Password successfully reset for {$user->email}.", null, $user);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Your password has been reset successfully! You can now log in with your new credentials.');
        }

        return back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    public function showProfile()
    {
        return view('auth.change-password');
    }

    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string|max:100',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:16',
                'confirmed',
                'different:current_password',
                'regex:/[A-Z]/',
                'regex:/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?~`]/',
                'regex:/(?:.*\d){3}/',
            ],
        ], [
            'password.min' => 'The new password must be at least 8 characters.',
            'password.max' => 'The new password must not exceed 16 characters.',
            'password.different' => 'The new password must be different from your current password.',
            'password.regex' => 'The password must contain at least 1 uppercase letter, 1 special character, and at least 3 digits.',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided current password does not match your existing password.']);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        AuditLog::record('update', 'auth', "Password changed by administrator: {$user->name} ({$user->email}).");

        return back()->with('success', 'Your password has been changed successfully!');
    }

    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100', 'regex:~^[\p{L}\s]+$~u'],
            'email' => [
                'nullable',
                'email:rfc',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                'max:100',
                'required_without:mobile',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'mobile' => [
                'nullable',
                'regex:/^[6-9][0-9]{9}$/',
                'required_without:email',
                Rule::unique('users', 'mobile')->ignore($user->id),
            ],
            'department' => ['nullable', 'string', 'min:2', 'max:100', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
        ], [
            'name.required' => 'Full Name is required.',
            'name.min' => 'Full Name must be at least :min characters.',
            'name.max' => 'Full Name cannot exceed :max characters.',
            'name.regex' => 'Full Name may only contain letters and spaces. Special characters are not allowed.',
            'email.email' => 'Please provide a valid official email address.',
            'email.regex' => 'Please provide a valid official email address with domain (e.g. name@company.com).',
            'email.required_without' => 'Either Email Address or Mobile Number is required.',
            'email.unique' => 'This email address is already in use by another user account.',
            'mobile.regex' => 'Mobile number must be a valid 10-digit number starting with 6, 7, 8, or 9.',
            'mobile.required_without' => 'Either Mobile Number or Email Address is required.',
            'mobile.unique' => 'This mobile number is already in use by another user account.',
            'department.regex' => 'Department may only contain letters, numbers, spaces, and allowed symbols (&, -, /, .).',
            'department.min' => 'Department must be at least :min characters.',
            'department.max' => 'Department cannot exceed :max characters.',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'mobile' => $validated['mobile'] ?? null,
            'department' => $validated['department'] ?? null,
        ]);

        AuditLog::record('update', 'auth', "Updated personal profile details: {$user->name} ({$user->email})", [
            'user_id' => $user->id,
            'changes' => $user->getChanges(),
        ]);

        return back()->with('profile_success', 'Your profile details have been updated successfully!');
    }
}

