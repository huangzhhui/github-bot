<?php


namespace App\Traits;


use App\Utils\GithubUrlBuilder;
use Psr\Http\Message\ResponseInterface;

trait CommentTrait
{

    protected function addComment(string $comment, string $repository = null, int $pullRequestId = null): ResponseInterface
    {
        $repository = $repository ?? $this->repository;
        $pullRequestId = $pullRequestId ?? $this->pullRequestId;
        $uri = GithubUrlBuilder::buildIssueUrl($repository, $pullRequestId) . '/comments';
        return $this->getClient()->post($uri, [
            'json' => [
                'body' => $comment,
            ],
        ]);
    }

}