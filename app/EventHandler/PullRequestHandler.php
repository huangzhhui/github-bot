<?php

declare(strict_types=1);
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\EventHandler;

use App\Event\ReceivedPullRequest;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

class PullRequestHandler extends AbstractHandler
{
    /**
     * @Inject
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @Inject
     * @var \Psr\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function handle(RequestInterface $request): ResponseInterface
    {
        if (! $request instanceof \Hyperf\HttpServer\Contract\RequestInterface) {
            return $this->response();
        }
        $this->logger->debug('Receive a new pull requests.');
        $response = $this->response()->withStatus(200);
        $event = new ReceivedPullRequest($request, $response);
        $this->eventDispatcher->dispatch($event);
        return $event->response;
    }
}
