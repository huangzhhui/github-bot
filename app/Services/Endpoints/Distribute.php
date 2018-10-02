<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Services\Endpoints;

class Distribute extends AbstractEnpoint
{
    /**
     * @var int
     */
    protected $pullRequestId;

    /**
     * @var string
     */
    protected $repository;

    /**
     * @var string
     */
    protected $project;

    public function __construct(string $repository, int $pullRequestId, string $body)
    {
        $this->repository = $repository;
        $this->pullRequestId = $pullRequestId;
        $this->project = $body;
    }

    public function __invoke()
    {
    }
}
