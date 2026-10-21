<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Resources\Auth\UserResource;
use Illuminate\Http\Request;
use Inertia\Middleware;

final class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => fn() => $request->user()
                    ? (new UserResource($request->user()))->resolve()
                    : null,
            ],
        ];
    }
}
