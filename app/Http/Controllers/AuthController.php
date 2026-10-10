<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Rate Limiting (5 attempts per minute per IP + email)
        $throttleKey = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withInput($request->only('email'))->with(
                'error',
                "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik."
            );
        }

        // Attempt Auth with active check
        $remember = $request->boolean('remember');
        $credentials['is_active'] = true;

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))->with(
                'success',
                'Selamat datang kembali, ' . Auth::user()->name . '!'
            );
        }

        RateLimiter::hit($throttleKey, 60);

        // Check if user exists but inactive
        $inactiveUser = \App\Models\User::where('email', $request->input('email'))
            ->where('is_active', false)
            ->first();

        if ($inactiveUser && Hash::check($request->input('password'), $inactiveUser->password)) {
            return back()->withInput($request->only('email'))->with(
                'error',
                'Akun Anda sedang dinonaktifkan oleh Administrator.'
            );
        }

        return back()->withInput($request->only('email'))->with(
            'error',
            'Email atau password yang Anda masukkan salah.'
        );
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Show change password form.
     */
    public function showChangePassword(): View
    {
        return view('auth.change-password');
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'confirmed', Password::min(6)],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'current_password.current_password' => 'Password lama Anda tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal harus 6 karakter.',
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.password')->with('success', 'Password Anda berhasil diperbarui!');
    }
}
