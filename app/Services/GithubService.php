<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Services;

use Swoft\Bean\Annotation\Bean;

/**
 * A service for Github API
 * @Bean()
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
