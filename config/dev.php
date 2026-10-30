<?php

declare(strict_types=1);

return [
    'user' => [
        'email' => env('DEV_USER_EMAIL', 'dev@tutora.test'),
        'first_name' => env('DEV_USER_FIRST_NAME', 'Dev'),
        'last_name' => env('DEV_USER_LAST_NAME', 'User'),
        'password' => env('DEV_USER_PASSWORD', 'password'),
    ],
];
