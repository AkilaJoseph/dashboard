<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            AuditLog::record('login', $user);
            if ($user->isAdmin()) {
                return redirect()->intended('/admin/dashboard');
            } elseif ($user->isOfficer()) {
                return redirect()->intended('/officer/dashboard');
            } else {
                return redirect()->intended('/student/dashboard');
            }
        }

        throw ValidationException::withMessages([
            'email' => __('The provided credentials do not match our records.'),
        ]);
    }

    public function logout(Request $request)
    {
        AuditLog::record('logout', Auth::user());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
