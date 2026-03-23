<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 30;

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Check DB-based account lockout before rate-limiting check
        $user = User::where('email', $request->email)->first();

        if ($user && $user->isLocked()) {
            throw ValidationException::withMessages([
                'email' => 'Your account is locked due to too many failed login attempts. Please try again in ' .
                    ceil($user->locked_until->diffInMinutes(now())) . ' minutes or contact an administrator.',
            ]);
        }

        try {
            $request->authenticate();
        } catch (ValidationException $e) {
            // Increment DB-based failed attempt counter
            if ($user) {
                $attempts = $user->login_attempts + 1;
                $lockedUntil = $attempts >= self::MAX_ATTEMPTS ? now()->addMinutes(self::LOCKOUT_MINUTES) : null;

                $user->update([
                    'login_attempts' => $attempts,
                    'locked_until'   => $lockedUntil,
                ]);

                if ($lockedUntil) {
                    throw ValidationException::withMessages([
                        'email' => 'Too many failed login attempts. Your account has been locked for ' . self::LOCKOUT_MINUTES . ' minutes.',
                    ]);
                }

                $remaining = self::MAX_ATTEMPTS - $attempts;
                if ($remaining > 0 && $remaining <= 2) {
                    throw ValidationException::withMessages([
                        'email' => trans('auth.failed') . " ({$remaining} attempt(s) remaining before lockout)",
                    ]);
                }
            }

            throw $e;
        }

        // Successful login — clear counters, record metadata
        if ($user) {
            $user->update([
                'login_attempts'  => 0,
                'locked_until'    => null,
                'last_login_at'   => now(),
                'last_login_ip'   => $request->ip(),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
