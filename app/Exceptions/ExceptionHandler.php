<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */

namespace App\Exceptions;

use Exception;
use Swoft\App;
use Swoft\Bean\Annotation\ExceptionHandler as EHandler;
use Swoft\Bean\Annotation\Handler;
use Swoft\Http\Message\Server\Response;

/**
 * @EHandler()
 */
class ExceptionHandler
{
    /**
     * @Handler(Exception::class)
     */
    public function handlerException(Response $response, \Throwable $throwable): Response
    {
        $file = $throwable->getFile();
        $line = $throwable->getLine();
        $code = $throwable->getCode();
        $exception = $throwable->getMessage();

        $data = ['message' => $exception, 'file' => $file, 'line' => $line, 'code' => $code];
        App::error(json_encode($data));
        return $response->json($data);
    }
}
