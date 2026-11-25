<?php

declare(strict_types=1);

return [

    'search' => ['placeholder' => 'Search by name or email...'],

    'table' => ['title' => 'Students'],

    'card' => [
        'email' => 'Email',
        'phone' => 'Phone',
        'next_lesson' => 'Next lesson',
        'view_calendar' => 'View calendar',
    ],

    'create' => [
        'trigger' => 'Add',
        'title' => 'New student',
        'fields' => [
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'email' => 'Email',
            'phone' => 'Phone',
            'timezone' => 'Timezone',
        ],
        'placeholders' => ['phone' => '+33 6 12 34 56 78'],
        'cancel' => 'Cancel',
        'save' => 'Save',
        'toast' => ['created' => 'Student created'],
    ],

];
