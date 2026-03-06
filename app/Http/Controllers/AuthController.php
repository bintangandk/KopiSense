<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetTokenMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

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

    /**
     * Show forgot password form.
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password.index');
    }

    /**
     * Generate reset token and send it to user email.
     */
    public function sendResetToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Email tidak ditemukan.',
            ])->onlyInput('email');
        }

        $throttleSeconds = (int) config('auth.passwords.users.throttle', 60);
        $existing = DB::table('password_reset_tokens')->where('email', $user->email)->first();

        if ($existing && $existing->created_at) {
            $secondsSinceLastToken = now()->diffInSeconds($existing->created_at);

            if ($secondsSinceLastToken < $throttleSeconds) {
                $waitSeconds = $throttleSeconds - $secondsSinceLastToken;

                return back()->withErrors([
                    'email' => "Silakan tunggu {$waitSeconds} detik sebelum meminta token baru.",
                ])->onlyInput('email');
            }
        }

        $token = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        Mail::mailer('mailersend')
            ->to($user->email)
            ->send(new PasswordResetTokenMail(
                $user->username,
                $token,
                (int) config('auth.passwords.users.expire')
            ));

        return redirect()->route('forgot-password.token', ['email' => $request->email])
            ->with('success', 'Token reset password berhasil dikirim ke email Anda.');
    }

    /**
     * Show token verification form.
     */
    public function showTokenForm(Request $request)
    {
        return view('auth.forgot-password.token-reset.index', [
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Validate token and redirect to reset password page.
     */
    public function verifyResetToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$this->isValidResetToken($request->email, $request->token)) {
            return back()->withErrors([
                'token' => 'Token tidak valid atau sudah kedaluwarsa.',
            ])->withInput();
        }

        return redirect()->route('reset-password.form', [
            'token' => $request->token,
            'email' => $request->email,
        ]);
    }

    /**
     * Show reset password form if token is valid.
     */
    public function showResetPasswordForm(Request $request, string $token)
    {
        $email = $request->query('email');
        $user = User::where('email', $email)->first();

        if (!$user || !$this->isValidResetToken($email, $token)) {
            return redirect()->route('forgot-password.token', ['email' => $email])
                ->withErrors([
                    'token' => 'Token tidak valid atau sudah kedaluwarsa.',
                ]);
        }

        return view('auth.forgot-password.reset-password.index', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Reset password using valid token.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|digits:6',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors([
                'email' => __($status),
            ])->withInput($request->only('email'));
        }

        return redirect()->route('login')->with('success', 'Password berhasil direset. Silakan login kembali.');
    }

    /**
     * Check whether reset token is valid and not expired.
     */
    private function isValidResetToken(string $email, string $token): bool
    {
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record) {
            return false;
        }

        $isExpired = now()->diffInMinutes($record->created_at) > config('auth.passwords.users.expire');

        if ($isExpired) {
            return false;
        }

        return Hash::check($token, $record->token);
    }
}
