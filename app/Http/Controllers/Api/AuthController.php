<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** JWT auth for the ORGANIZER app. Visitors do not log in. */
class AuthController extends Controller
{
    /** `login` can be email or mobile. */
    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        $token = auth('api')->attempt([
            $field => $data['login'],
            'password' => $data['password'],
            'is_active' => true,
        ]);

        if (! $token) {
            throw ValidationException::withMessages(['login' => ['Invalid credentials.']]);
        }

        return $this->respond($token, auth('api')->user());
    }

    public function me()
    {
        return response()->json($this->userPayload(auth('api')->user()));
    }

    /** Not behind auth:api, so an expired (but still refreshable) token works. */
    public function refresh()
    {
        $token = auth('api')->refresh();

        return $this->respond($token, auth('api')->setToken($token)->user());
    }

    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Logged out']);
    }

    private function respond(string $token, User $user, int $status = 200)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $this->userPayload($user),
        ], $status);
    }

    private function userPayload(User $user): array
    {
        return $user->only('id', 'name', 'email', 'mobile', 'role', 'department');
    }
}
