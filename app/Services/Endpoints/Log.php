<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */

namespace App\Services\Endpoints;

use Swoft\App;

/**
 * This command is use to log the request send by Github.
 */
class Log extends AbstractEnpoint
{

    /**
     * @var array
     */
    protected $target;

    public function __construct(array $target)
    {
        $this->target = $target;
    }

    public function __invoke()
    {
        App::info(json_encode($this->target));
    }

}
