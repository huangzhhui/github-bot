<?php


namespace App\Traits;


use GuzzleHttp\Client;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Guzzle\ClientFactory;

trait ClientTrait
{

    /**
     * @Inject()
     * @var ClientFactory
     */
    protected $clientFactory;

    protected function getClient(string $baseUri = 'https://api.github.com'): Client
    {
        return $this->clientFactory->create([
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