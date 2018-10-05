<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Services\Endpoints;

use App\Services\EndpointInterface;
use App\Utils\GithubClientBuilder;
use App\Utils\GithubUrlBuilder;
use Psr\Http\Message\ResponseInterface;
use Swoft\Helper\StringHelper;
use Swoft\HttpClient\Client;

abstract class AbstractEnpoint implements EndpointInterface
{

    protected function getClient(): Client
    {
        return GithubClientBuilder::create();
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
            if (StringHelper::startsWith($user, '@')) {
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
