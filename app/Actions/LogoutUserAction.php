<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Session\Session;

final readonly class LogoutUserAction
{
    public function __construct(
        private StatefulGuard $statefulGuard,
        private Session $session,
    ) {}

    public function handle(): void
    {
        $this->statefulGuard->logout();
        $this->session->invalidate();
        $this->session->regenerateToken();
    }
}
