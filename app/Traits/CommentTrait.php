<?php

declare(strict_types=1);
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Traits;

use App\Utils\GithubUrlBuilder;
use Psr\Http\Message\ResponseInterface;

trait CommentTrait
{
    protected function addComment(string $comment, string $repository = null, int $pullRequestId = null): ResponseInterface
    {
        $repository = $repository ?? $this->repository;
        $pullRequestId = $pullRequestId ?? $this->pullRequestId;
        $comment = config('github.comment.header') . $comment;
        $uri = GithubUrlBuilder::buildIssueUrl($repository, $pullRequestId) . '/comments';
        return $this->getClient()->post($uri, [
            'json' => [
                'body' => $comment,
            ],
        ]);
    }
}
