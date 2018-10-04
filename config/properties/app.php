<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */

return [
    'env' => env('APP_ENV', 'test'),
    'debug' => env('APP_DEBUG', false),
    'version' => '1.0',
    'autoInitBean' => true,
    'bootScan' => [],
    'excludeScan' => [],
    'github' => [
        'user_agent' => env('GITHUB_USER_AGENT', 'github-bot'),
        'access_token' => env('GITHUB_ACCESS_TOKEN', ''),
        'webhook' => [
            'secret' => env('GITHUB_WEBHOOK_SECRET', ''),
        ],
        'merge' => [
            'method' => env('GITHUB_MERGE_METHOD', 'squash'),
        ],
        'release' => [
            'message_cate' => [
                'added' => ['feat:', 'feature:', 'add:'],
                'changed' => ['change:', 'refactor:', 'modify:'],
                'fixed' => ['fix:', 'fixed:'],
                'deprecated' => ['deprecated:'],
                'removed' => ['remove:', 'delete:'],
            ],
            'message_template' => '@configs/templates/release.php',
            'repository_alias' => [],
        ],
    ],
];
