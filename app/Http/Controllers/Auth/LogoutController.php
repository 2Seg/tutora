<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\LogoutUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

final readonly class LogoutController
{
    public function __construct(
        private LogoutUser $logoutUser,
        private Redirector $redirector,
    ) {}

    public function __invoke(): RedirectResponse
    {
        $this->logoutUser->handle();

        return $this->redirector->to('/login');
    }
}
