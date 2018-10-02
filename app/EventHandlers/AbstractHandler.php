<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\EventHandlers;

use Swoft\Http\Message\Server\Request;

abstract class AbstractHandler
{
    abstract public function handle(Request $request);
}
