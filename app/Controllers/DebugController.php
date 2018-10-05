<?php

namespace App\Controllers;

use App\EventHandlers\EventHandlerManager;
use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\Value;
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
     * @Value(name="${config.github.debug.auth}")
     * @var string
     */
    protected $debugAuth;

    /**
     * @RequestMapping("/github-bot/debug")
     */
    public function debug(Request $request)
    {
        if (! $request->hasHeader('Authorization') || $request->getHeaderLine('Authorization') !== $this->getDebugAuth()) {
            return response()->withStatus('401');
        }
        $event = $request->input('event');
        $handler = $this->eventHandlerManager->getHandler($event);
        return $handler->handle($request);
    }

    public function getDebugAuth(): string
    {
        return $this->debugAuth;
    }

}