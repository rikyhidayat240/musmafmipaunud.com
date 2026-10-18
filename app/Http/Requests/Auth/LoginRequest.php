<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Timebox;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        Auth::login($this->validateCredentials());
    }

    /**
     * Validate NIM/email + password WITHOUT logging the user in.
     * Dipakai agar tantangan 2FA dapat diberikan sebelum sesi login dibuat.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateCredentials(): Authenticatable
    {
        $this->ensureIsNotRateLimited();

        $login = (string) $this->email;
        $password = (string) $this->password;
        $provider = Auth::guard('web')->getProvider();

        $user = (new Timebox())->call(function () use ($provider, $login, $password) {
            foreach (['nim', 'email'] as $field) {
                $candidate = $provider->retrieveByCredentials([$field => $login]);
                if ($candidate && $provider->validateCredentials($candidate, ['password' => $password])) {
                    if (config('hashing.rehash_on_login', true) && method_exists($provider, 'rehashPasswordIfRequired')) {
                        $provider->rehashPasswordIfRequired($candidate, ['password' => $password]);
                    }

                    return $candidate;
                }
            }

            return null;
        }, 200000);

        if (! $user) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'NIM/Email atau kata sandi yang Anda masukkan salah.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return $user;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
