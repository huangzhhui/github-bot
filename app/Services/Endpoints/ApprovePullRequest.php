<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Services\Endpoints;

class ApprovePullRequest extends RequestChanges
{
    public function __invoke()
    {
        $this->addApprovedComment($this->repository, $this->pullRequestId);
        $this->review('APPROVE');
    }
}
