<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\EventHandler;

use App\Event\ReceivedPullRequest;
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
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @Inject()
     * @var \Psr\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function handle(RequestInterface $request)
    {
        if (! $request instanceof \Hyperf\HttpServer\Contract\RequestInterface || ! $this->enable) {
            return;
        }
        $this->logger->debug('Receive a new pull requests.');
        $response = $this->response()->withStatus(200);
        $event = new ReceivedPullRequest($request, $response);
        $this->eventDispatcher->dispatch($event);
        return $event->response;
    }

}
