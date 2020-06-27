<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\EventHandler;

use App\Exception\EventHandlerNotExistException;
use Hyperf\Di\Annotation\Inject;
use Psr\Log\LoggerInterface;

class EventHandlerManager
{
    /**
     * Event handlers mapping
     *
     * @var array
     */
    protected $events = [
        'issue_comment' => IssueCommentHandler::class,
        'pull_request_review' => PullRequestReviewHandler::class,
    ];

    /**
     * @Inject()
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Get the specified event handler.
     *
     * @throws EventHandlerNotExistException
     */
    public function getHandler(string $event): AbstractHandler
    {
        $this->logger->debug(sprintf('Receive a %s event', $event));
        if (! isset($this->events[$event])) {
            throw new EventHandlerNotExistException('Event handler not exist.');
        }
        $handler = new ($this->events[$event]);
        if (! $handler instanceof AbstractHandler) {
            throw new EventHandlerNotExistException('It is not a valid event handler.');
        }
        return $handler;
    }
}
