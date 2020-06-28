<?php

declare(strict_types=1);
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Service;

/**
 * A service for Github API.
 */
class GithubService
{
    public const HEADER_SIGNATURE = 'x-hub-signature';

    public const HEADER_EVENT = 'x-github-event';

    public function execute(EndPointInterface $endPoint): void
    {
        $endPoint();
    }
}
