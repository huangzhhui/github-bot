<?php

declare(strict_types=1);

namespace App\Listener;

use App\Event\ReceivedPullRequest;
use App\Traits\ClientTrait;
use App\Traits\CommentTrait;
use App\Utils\GithubUrlBuilder;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Annotation\Listener;
use Psr\Container\ContainerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Log\LoggerInterface;

/**
 * @Listener
 */
class ClosePullRequestListener implements ListenerInterface
{

    use ClientTrait, CommentTrait;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $enable;

    /**
     * @var array
     */
    protected $excepts;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(LoggerInterface::class);
        $config = $container->get(ConfigInterface::class);
        $this->enable = $config->get('github.pr-auto-close.enable', false);
        $this->excepts = $config->get('github.pr-auto-close.excepts', []);
    }

    public function listen(): array
    {
        return [
            ReceivedPullRequest::class,
        ];
    }

    /**
     * @param \App\Event\ReceivedPullRequest $event
     */
    public function process(object $event)
    {
        $request = $event->request;
        $response = $event->response;
        try {
            $repository = $request->input('repository.full_name', '');
            if (! $this->isHyperfComponentRepo($repository)) {
                // Should not close this PR automatically.
                $response = $response->withStatus(200);
            }
            $pullRequestId = $request->input('number', 0);
            $currentState = $request->input('pull_request.state', '');
            $senderName = $request->input('sender.login', '');
            if ($currentState === 'closed') {
                return;
            }
            $commentResult = $this->addClosedComment($repository, $pullRequestId, $senderName);
            if ($commentResult) {
                $this->logger->info(sprintf('Pull Request %s#%d added auto comment.', $repository, $pullRequestId));
            } else {
                $this->logger->warning(sprintf('Pull Request %s#%d add auto comment failed.', $repository, $pullRequestId));
            }
            $closeResult = $this->closePullRequest($repository, $pullRequestId, $currentState);
            if ($closeResult) {
                $this->logger->info(sprintf('Pull Request %s#%d has been closed.', $repository, $pullRequestId));
            } else {
                $this->logger->warning(sprintf('Pull Request %s#%d close failed.', $repository, $pullRequestId));
            }
        } catch (\Throwable $e) {
            $response = $response->withStatus(500)->withHeader('Exception-Message', $e->getMessage());
        } finally {
            $event->response = $response;
        }
    }

    protected function isHyperfComponentRepo(string $repository): bool
    {
        return ! in_array($repository, $this->excepts);
    }

    protected function closePullRequest(string $repository, int $pullRequestId): bool
    {
        if (! $repository || $pullRequestId === 0) {
            return false;
        }
        $this->logger->debug(sprintf('Pull Request %s#%d is closing.', $repository, $pullRequestId));
        $response = $this->getClient()->patch(GithubUrlBuilder::buildPullRequestUrl($repository, $pullRequestId), [
            'json' => [
                'state' => 'closed',
            ],
        ]);
        return $response->getStatusCode() === 200;
    }

    protected function addClosedComment(string $repository, int $pullRequestId, string $senderName): bool
    {
        $senderName = $senderName ? '@' . $senderName : '';
        $comment = "Hi $senderName, this is a READ-ONLY repository, please submit your Pull Request to [hyperf/hyperf](https://github.com/hyperf/hyperf) repository, this Pull Request will close automatically.";
        $response = $this->addComment($comment, $repository, $pullRequestId);
        return $response->getStatusCode() === 200;
    }
}
