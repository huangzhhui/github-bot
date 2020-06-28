<?php

declare(strict_types=1);
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Service\Endpoints;

use App\Utils\GithubUrlBuilder;
use Swoole\Coroutine;

class NeedReview extends AbstractEnpoint
{
    /**
     * @var int
     */
    protected $pullRequestId;

    /**
     * @var string
     */
    protected $repository;

    /**
     * @var string
     */
    protected $body;

    public function __construct(string $repository, int $pullRequestId, string $body)
    {
        $this->repository = $repository;
        $this->pullRequestId = $pullRequestId;
        $this->body = $body;
    }

    public function __invoke()
    {
        $client = $this->getClient();
        $assigneesUrl = GithubUrlBuilder::buildReviewRequestUrl($this->repository, $this->pullRequestId);
        $reviewers = $this->parseTargetUsers();
        if (! $reviewers) {
            return;
        }
        $response = $client->post($assigneesUrl, [
            'json' => [
                'reviewers' => $reviewers,
            ],
        ]);
        if ($response->getStatusCode() !== 201) {
            Coroutine::sleep(10);
            $this->addSorryComment();
        }
    }

    private function addSorryComment(): void
    {
        $this->addComment('( Ĭ ^ Ĭ ) Assign the reviewers failed, sorry ~~~');
    }
}
