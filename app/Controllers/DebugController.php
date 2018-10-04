<?php

namespace App\Controllers;

use App\EventHandlers\EventHandlerManager;
use Swoft\Bean\Annotation\Inject;
use Swoft\Http\Message\Server\Request;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;


/**
 * @Controller()
 */
class DebugController
{

    /**
     * @Inject()
     * @var EventHandlerManager
     */
    protected $eventHandlerManager;

    /**
     * @RequestMapping("/github-bot/debug")
     */
    public function debug(Request $request)
    {
        $event = $request->input('event');
        $handler = $this->eventHandlerManager->getHandler($event);
        return $handler->handle($request);
    }

}