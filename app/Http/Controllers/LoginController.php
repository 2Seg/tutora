<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AuthenticateUser;
use App\Http\Requests\LoginRequest;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

final readonly class LoginController
{
    public function __construct(
        private Redirector $redirector,
        private AuthenticateUser $authenticateUser,
        private UrlGenerator $urlGenerator,
    ) {}

    public function login(LoginRequest $loginRequest): RedirectResponse
    {
        $credentials = $loginRequest->safe()->only(['email', 'password']);
        $remember = $loginRequest->boolean('remember');

        $this->authenticateUser->handle($credentials, $remember);

        return $this->redirector->intended(
            $this->urlGenerator->route('teacher.dashboard')
        );
    }
}
