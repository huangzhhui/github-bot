<?php

namespace App\Event;


use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class ReceivedPullRequest
{

    /**
     * @var \Hyperf\HttpServer\Contract\RequestInterface
     */
    public $request;

    /**
     * @var \Hyperf\HttpServer\Contract\ResponseInterface
     */
    public $response;

    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

}