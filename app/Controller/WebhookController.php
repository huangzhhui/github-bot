<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Controller;

use App\EventHandler\EventHandlerManager;
use App\Service\GithubService;
use App\Service\SignatureService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * @Controller(prefix="/github-bot")
 */
class WebhookController extends AbstractController
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
    public function callback()
    {
        if (! $this->isValidWebhook($this->request)) {
            return $this->response->withStatus(400);
        }
        $event = $this->request->getHeaderLine(GithubService::HEADER_EVENT);
        if (! $this->signatureService->isValid($this->request)) {
            return $this->response->withStatus(401);
        }
        $handler = $this->eventHandlerManager->getHandler($event);
        return $handler->handle($this->request);
    }

    private function isValidWebhook(RequestInterface $request): bool
    {
        return $request->hasHeader(GithubService::HEADER_EVENT) && $request->hasHeader(GithubService::HEADER_SIGNATURE) && $request->getHeaderLine('content-type') === 'application/json';
    }
}
