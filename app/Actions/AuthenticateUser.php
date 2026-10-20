<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Session\Session;
use Illuminate\Validation\ValidationException;

final class AuthenticateUser
{
    public function __construct(
        private readonly AuthManager $authManager,
        private readonly Session $session,
    ) {}

    public function attempt(array $credentials, bool $remember = false): void
    {
        if (! $this->authManager->attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'global' => 'These credentials do not match our records.',
            ]);
        }

        $this->session->regenerate();
    }
}
