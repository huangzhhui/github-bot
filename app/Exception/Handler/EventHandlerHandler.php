<?php

namespace App\Exception\Handler;


use App\Exception\EventHandlerNotExistException;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class EventHandlerHandler extends ExceptionHandler
{

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->stopPropagation();
        return $response->withStatus(200)->withHeader('Event-Handler', 'none');
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof EventHandlerNotExistException;
    }
}