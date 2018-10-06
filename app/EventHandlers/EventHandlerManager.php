<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\EventHandlers;

use App\Exceptions\EventHandlerNotExistException;
use Psr\Log\LoggerInterface;
use Swoft\Bean\Annotation\Bean;
use function bean;
use Swoft\Bean\Annotation\Inject;

/**
 * @Bean()
 */
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
     * @Inject("logger")
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
        $handler = bean($this->events[$event]);
        if (! $handler instanceof AbstractHandler) {
            throw new EventHandlerNotExistException('It is not a valid event handler.');
        }
        return $handler;
    }
}
