<?php
return [
    'user_agent' => env('GITHUB_USER_AGENT', 'github-bot'),
    'access_token' => env('GITHUB_ACCESS_TOKEN', ''),
    'webhook' => [
        'secret' => env('GITHUB_WEBHOOK_SECRET', ''),
    ],
    'debug' => [
        'auth' => env('GITHUB_DEBUG_AUTH', ''),
    ],
    'merge' => [
        'method' => env('GITHUB_MERGE_METHOD', 'squash'),
    ],
];