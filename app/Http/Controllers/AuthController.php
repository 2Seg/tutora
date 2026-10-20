<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AuthenticateUser;
use App\Actions\LogoutUser;
use App\Http\Requests\LoginRequest;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

final readonly class AuthController
{
    public function __construct(
        private AuthenticateUser $authenticateUser,
        private LogoutUser $logoutUser,
        private Redirector $redirector,
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

    public function logout(): RedirectResponse
    {
        $this->logoutUser->handle();

        return $this->redirector->to('/');
    }
}
