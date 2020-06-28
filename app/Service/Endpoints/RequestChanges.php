<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Service\Endpoints;

use App\Utils\GithubClientBuilder;
use App\Utils\GithubUrlBuilder;
use Psr\Http\Message\ResponseInterface;

class RequestChanges extends AbstractEnpoint
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
        return $this->review('REQUEST_CHANGES');
    }

    protected function review($status): ResponseInterface
    {
        $pullRequestUrl = GithubUrlBuilder::buildPullRequestUrl($this->repository, $this->pullRequestId) . '/reviews';
        return $this->getClient()->post($pullRequestUrl, [
            'json' => [
                'body' => $this->body,
                'event' => $status,
            ]
        ]);
    }
}
