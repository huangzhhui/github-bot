<?php


namespace App\Traits;


use GuzzleHttp\Client;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Utils\ApplicationContext;

trait ClientTrait
{

    protected function getClient(string $baseUri = 'https://api.github.com'): Client
    {
        $clientFactory = ApplicationContext::getContainer()->get(ClientFactory::class);
        return $clientFactory->create([
            'base_uri' => $baseUri,
            'headers' => [
                'User-Agent' => config('github.user_agent', 'Github-Bot'),
                'Authorization' => 'token ' . config('github.access_token'),
            ],
            '_options' => [
                'timeout' => 60,
            ],
        ]);
    }

}