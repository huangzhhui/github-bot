<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Utils;

use Swoft\HttpClient\Client;

/**
 * A builder for create a http client of Github API
 */
class GithubClientBuilder
{
    public static function create(string $baseUri = 'https://api.github.com')
    {
        return new Client([
            'base_uri' => $baseUri,
            'headers' => [
                'User-Agent' => config('github.user_agent', 'Github-Bot'),
                'Authorization' => 'token ' . config('github.access_token'),
            ],
            '_options' => [
                'timeout' => 20,
            ],
        ]);
    }
}
