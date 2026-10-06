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
            if (in_array($user->role, ['superadmin', 'admin'], true)) {
                $request->session()->regenerate();
                AuditLog::record('login', 'auth', 'Administrator successfully logged into operations panel.', [
                    'login_field' => $field,
                    'ip' => $request->ip(),
                ]);

                return redirect()->intended(route('dashboard'));
            }

            if ($user->role === 'organizer') {
                $request->session()->regenerate();
                AuditLog::record('login', 'auth', "Organizer {$user->name} logged into organizer portal.", [
                    'login_field' => $field,
                    'ip' => $request->ip(),
                ]);

                return redirect()->intended(route('mobile.app'));
            }

            AuditLog::record('denied', 'auth', 'Login attempt denied: account not authorized.', [
                'user' => $user->email,
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

    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string|max:100',
            'password' => 'required|string|min:8|max:100|confirmed|different:current_password',
        ], [
            'password.different' => 'The new password must be different from your current password.',
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
}

