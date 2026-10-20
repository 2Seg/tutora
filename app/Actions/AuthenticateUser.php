<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Session\Session;
use Illuminate\Validation\ValidationException;

final readonly class AuthenticateUser
{
    public function __construct(
        private AuthManager $authManager,
        private Session $session,
    ) {}

    public function handle(array $credentials, bool $remember = false): void
    {
        if (! $this->authManager->attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'global' => 'These credentials do not match our records.',
            ]);
        }

        $this->session->regenerate();
    }
}
