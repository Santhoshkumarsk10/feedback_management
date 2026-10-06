<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        if (Auth::attempt([$field => $data['login'], 'password' => $data['password'], 'is_active' => true], $request->boolean('remember'))) {
            if (in_array(Auth::user()->role, ['superadmin', 'admin'], true)) {
                $request->session()->regenerate();
                \App\Models\AuditLog::record('login', 'auth', 'Administrator successfully logged into operations panel.', [
                    'login_field' => $field,
                    'ip' => $request->ip(),
                ]);

                return redirect()->intended(route('dashboard'));
            }
            \App\Models\AuditLog::record('denied', 'auth', 'Login attempt denied: account not authorized for admin panel.', [
                'user' => Auth::user()->email,
            ]);
            Auth::logout();
        }

        return back()->withErrors(['login' => 'Invalid credentials, or this account has no admin access.'])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            \App\Models\AuditLog::record('logout', 'auth', 'User logged out of active session.');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
