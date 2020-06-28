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
    'comment' => [
        'header' => "**[This message is created by [hyperf-bot](https://github.com/huangzhhui/github-bot)]**\r\n\r\n"
    ],
    'pr-auto-close' => [
        'enable' => env('GITHUB_PR_AUTO_CLOSE', false),
        // The PRs of projects that in `excepts` will not close automatically, even enable is true.
        'excepts' => [
            'hyperf/hyperf',
            'hyperf/hyperf-skeleton',
            'hyperf/nano',
        ],
    ],
];