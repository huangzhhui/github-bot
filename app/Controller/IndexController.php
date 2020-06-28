<?php

declare(strict_types=1);
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * @Controller
 */
class IndexController extends AbstractController
{
    /**
     * @RequestMapping("/")
     */
    public function index(): array
    {
        return [
            'Hello Github Bot. Build by Hyperf.',
        ];
    }
}
