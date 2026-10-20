<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Session\Session;
use Illuminate\Validation\ValidationException;

final readonly class AuthenticateUser
{
    public function __construct(
        private StatefulGuard $guard,
        private Session $session,
    ) {}

    public function handle(array $credentials, bool $remember = false): void
    {
        if (! $this->guard->attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'global' => 'These credentials do not match our records.',
            ]);
        }

        $this->session->regenerate();
    }
}
