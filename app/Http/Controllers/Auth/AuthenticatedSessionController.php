<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\BallotPreparer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): SymfonyResponse
    {
        /** @var User $user */
        $user = $request->validateCredentials();

        if ($this->requiresTwoFactorChallenge($user)) {
            $request->session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => $request->boolean('remember'),
            ]);

            TwoFactorAuthenticationChallenged::dispatch($user);

            return $request->wantsJson()
                ? response()->json(['two_factor' => true])
                : redirect()->route('two-factor.login');
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        app(BallotPreparer::class)->prepareFor($user);

        return redirect()->intended(route('home', absolute: false));
    }

    /**
     * Determine whether the authenticated user must complete a Fortify 2FA challenge.
     */
    protected function requiresTwoFactorChallenge(User $user): bool
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            return false;
        }

        if (! $user->two_factor_secret || ! in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user))) {
            return false;
        }

        if (Fortify::confirmsTwoFactorAuthentication()) {
            return ! is_null($user->two_factor_confirmed_at);
        }

        return true;
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
