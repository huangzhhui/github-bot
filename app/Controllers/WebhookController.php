<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Controllers;

use App\EventHandlers\EventHandlerManager;
use App\Services\GithubService;
use App\Services\SignatureService;
use Swoft\Bean\Annotation\Inject;
use Swoft\Http\Message\Server\Request;
use Swoft\Http\Message\Server\Response;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;

/**
 * @Controller(prefix="/github-bot")
 */
class WebhookController
{
    /**
     * @Inject()
     * @var SignatureService
     */
    protected $signatureService;

    /**
     * @Inject()
     * @var EventHandlerManager
     */
    protected $eventHandlerManager;

    /**
     * @RequestMapping("webhook")
     */
    public function callback(Request $request, Response $response)
    {
        if (! $this->isValidWebhook($request)) {
            return $response->withStatus(400);
        }
        $event = $request->getHeaderLine(GithubService::HEADER_EVENT);
        if (! $this->signatureService->isValid($request)) {
            return $response->withStatus(401);
        }
        $handler = $this->eventHandlerManager->getHandler($event);
        return $handler->handle($request);
    }

    private function isValidWebhook(Request $request): bool
    {
        return $request->hasHeader(GithubService::HEADER_EVENT) && $request->hasHeader(GithubService::HEADER_SIGNATURE) && $request->getContentType() === 'application/json';
    }
}
