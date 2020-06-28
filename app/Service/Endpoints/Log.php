<?php

declare(strict_types=1);
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Service\Endpoints;

use Hyperf\Di\Annotation\Inject;

/**
 * This command is use to log the request send by Github.
 */
class Log extends AbstractEnpoint
{
    /**
     * @Inject
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

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
        $this->logger->info(json_encode($this->target));
    }
}
