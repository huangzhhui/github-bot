<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Service\Endpoints;

use App\Service\EndpointInterface;
use App\Utils\GithubClientBuilder;
use App\Utils\GithubUrlBuilder;
use GuzzleHttp\Client;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Utils\Str;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractEnpoint implements EndpointInterface
{

    /**
     * @var ClientFactory
     */
    protected $clientFactory;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    protected function getClient(): Client
    {
        $baseUri = 'https://api.github.com';
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

    protected function addComment(string $comment): ResponseInterface
    {
        $uri = GithubUrlBuilder::buildIssueUrl($this->repository, $this->pullRequestId) . '/comments';
        return $this->getClient()->post($uri, [
            'json' => [
                'body' => $comment,
            ],
        ])->getResponse();
    }

    /**
     * Parse the target users from the body of request.
     */
    protected function parseTargetUsers(): array
    {
        $reviewers = [];
        $explodedBody = explode(' ', $this->body);
        foreach ($explodedBody as $user) {
            if (Str::startsWith($user, '@')) {
                $reviewers[] = substr($user, 1);
            }
        }
        return array_unique($reviewers);
    }

    protected function addApprovedComment($repository, $pullRequestId): void
    {
        try {
            $uri = GithubUrlBuilder::buildReviewsUrl($repository, $pullRequestId);
            $response = $this->getClient()->get($uri)->getResponse();
            if ($response->getStatusCode() === 200 && $content = $response->getBody()->getContents()) {
                $approvedUsers = [];
                $decodedBody = json_decode($content, true);
                foreach ($decodedBody ?? [] as $review) {
                    if (isset($review['user']['login'], $review['state']) && $review['state'] === 'APPROVED') {
                        $approvedUsers[$review['user']['login']] = '[' . $review['user']['login'] . '](' . $review['html_url'] . ')';
                    }
                }
                if ($approvedUsers) {
                    $comment = "[APPROVAL NOTIFIER] This pull-request is **APPROVED**\r\n\r\nThis pull-request has been approved by: " . implode(' ', $approvedUsers);
                    $this->addComment($comment);
                }
            }
        } catch (\Throwable $e) {
        }
    }
}
