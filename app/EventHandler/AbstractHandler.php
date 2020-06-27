<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\EventHandler;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\Http\Message\RequestInterface;

abstract class AbstractHandler
{
    abstract public function handle(RequestInterface $request);

    protected function response(): ResponseInterface
    {
        return ApplicationContext::getContainer()->get(ResponseInterface::class);
    }
}