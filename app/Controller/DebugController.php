<?php

namespace App\Controller;

use App\EventHandler\EventHandlerManager;
use Hyperf\Config\Annotation\Value;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;


/**
 * @Controller()
 */
class DebugController extends AbstractController
{

    /**
     * @Inject()
     * @var EventHandlerManager
     */
    protected $eventHandlerManager;

    /**
     * @Value("github.debug.auth")
     * @var string
     */
    protected $debugAuth;

    /**
     * @RequestMapping("/github-bot/debug")
     */
    public function debug()
    {
        if (! $this->request->hasHeader('Authorization') || $this->request->getHeaderLine('Authorization') !== $this->getDebugAuth()) {
            return $this->response->withStatus('401');
        }
        $event = $this->request->input('event');
        $handler = $this->eventHandlerManager->getHandler($event);
        return $handler->handle($this->request);
    }

    public function getDebugAuth(): string
    {
        return $this->debugAuth;
    }

}