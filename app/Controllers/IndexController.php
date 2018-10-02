<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */

namespace App\Controllers;

use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;

/**
 * @Controller()
 */
class IndexController
{
    /**
     * @RequestMapping("/")
     */
    public function index(): array
    {
        return [
            'Hello Bot.'
        ];
    }
}
