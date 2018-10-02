<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\EventHandlers;

use App\Exceptions\EventHandlerNotExistException;
use Swoft\Bean\Annotation\Bean;
use function bean;

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
     * Get the specified event handler.
     *
     * @throws EventHandlerNotExistException
     */
    public function getHandler(string $event): AbstractHandler
    {
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
