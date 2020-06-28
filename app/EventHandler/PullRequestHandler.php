<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\EventHandler;

use App\Traits\ClientTrait;
use App\Traits\CommentTrait;
use App\Utils\GithubUrlBuilder;
use Hyperf\Config\Annotation\Value;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class PullRequestHandler extends AbstractHandler
{

    use ClientTrait, CommentTrait;

    /**
     * @Inject()
     * @var CommandManager
     */
    protected $commandManager;

    /**
     * @Inject()
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @Value("github.pr-auto-close.enable")
     * @var bool
     */
    protected $enable = false;

    /**
     * @Value("github.pr-auto-close.excepts")
     * @var array
     */
    protected $excepts = [];

    public function handle(RequestInterface $request)
    {
        if (! $request instanceof \Hyperf\HttpServer\Contract\RequestInterface || ! $this->enable) {
            return;
        }
        $this->logger->debug('Receive a new pull requests.');
        $repository = $request->input('repository.full_name', '');
        if (! $this->isHyperfComponentRepo($repository)) {
            // Should not close this PR automatically.
            return $this->response()->withStatus(200);
        }
        $pullRequestId = $request->input('number', 0);
        $currentState = $request->input('pull_request.state', '');
        try {
            retry(3, function () use ($repository, $pullRequestId, $currentState) {
                if ($currentState === 'closed') {
                    return;
                }
                $commentResult = $this->addClosedComment($repository, $pullRequestId);
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
            }, 5);
        } catch (Throwable $e) {
            // Do nothing
        }
        return $this->response()->withStatus(200);
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

    protected function addClosedComment(string $repository, int $pullRequestId): bool
    {
        $comment = "[This is a message created by hyperf-bot]\r\nPlease submit your Pull Request to [hyperf/hyperf](https://github.com/hyperf/hyperf) repository, this Pull Request will close automatically.";
        $response = $this->addComment($comment, $repository, $pullRequestId);
        return $response->getStatusCode() === 200;
    }

}
