<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login.index');
    }

    /**
     * Handle login with username or email
     */
    public function login(Request $request)
    {
        $request->validate([
            'email-username' => 'required|string',
            'password' => 'required|string',
        ]);

        $input = $request->input('email-username');
        $password = $request->input('password');

        // Try to authenticate with email or username
        if (
            Auth::attempt(['email' => $input, 'password' => $password]) ||
            Auth::attempt(['username' => $input, 'password' => $password])
        ) {
            $request->session()->regenerate();
            return redirect()->route('dashboard')->with('success', 'Anda berhasil login!');
        }

        return back()->withErrors([
            'email-username' => 'Email/Username atau password salah.',
        ])->onlyInput('email-username');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Anda berhasil logout.');
    }
}
