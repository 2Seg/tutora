<?php

declare(strict_types=1);

namespace App\Actions;

use App\Exceptions\InvalidCredentials;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Session\Session;

final readonly class AuthenticateUser
{
    public function __construct(
        private StatefulGuard $guard,
        private Session       $session,
    ) {}

    public function handle(array $credentials, bool $remember = false): void
    {
        if (!$this->guard->attempt($credentials, $remember)) {
            throw new InvalidCredentials();
        }

        $this->session->regenerate();
    }
}
