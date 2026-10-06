<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

/**
 * JWT auth for the ORGANIZER app. Visitors do not log in.
 */
class AuthController extends Controller
{
    /**
     * `login` can be email or mobile.
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        $token = $this->guard()->attempt([
            $field => $data['login'],
            'password' => $data['password'],
            'is_active' => true,
        ]);

        if (!$token) {
            throw ValidationException::withMessages(['login' => ['Invalid credentials.']]);
        }

        return $this->respond($token, $this->guard()->user());
    }

    public function me()
    {
        return response()->json($this->userPayload($this->guard()->user()));
    }

    /**
     * Not behind auth:api, so an expired (but still refreshable) token works.
     */
    public function refresh()
    {
        $token = $this->guard()->refresh();

        return $this->respond($token, $this->guard()->setToken($token)->user());
    }

    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Logged out']);
    }

    /**
     * Change password for logged in API user (organizer)
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'password.different' => 'The new password must be different from your current password.',
        ]);

        /** @var User $user */
        $user = $this->guard()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided current password does not match.',
            ], 422);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        AuditLog::record('update', 'auth', "API user changed password: {$user->name} ({$user->email}).", null, $user);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    }

    /**
     * Request password reset token / email
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
        ]);

        $login = trim($request->login);
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        $user = User::where($field, $login)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No active user found with that email or mobile number.',
            ], 404);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This account is deactivated. Please contact your administrator.',
            ], 403);
        }

        $token = Password::createToken($user);
        $user->sendPasswordResetNotification($token);

        AuditLog::record('request', 'auth', "Password reset requested via API for {$user->email}.", null, $user);

        return response()->json([
            'success' => true,
            'message' => 'Password reset instructions have been dispatched to ' . $user->email . '.',
            'email' => $user->email,
            'dev_token' => app()->isLocal() ? $token : null,
        ]);
    }

    /**
     * Reset password using token
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                AuditLog::record('update', 'auth', "Password reset via API for {$user->email}.", null, $user);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully. You can now login with your new credentials.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __($status),
        ], 422);
    }

    private function respond(string $token, User $user, int $status = 200)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->getTTL() * 60,
            'user' => $this->userPayload($user),
        ], $status);
    }

    private function userPayload(User $user): array
    {
        return $user->only('id', 'name', 'email', 'mobile', 'role', 'department');
    }

    /**
     * Get the authenticated JWT guard.
     */
    private function guard(): JWTGuard
    {
        /** @var JWTGuard */
        return auth('api');
    }
}
