<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\EventHandlers;

use App\Services\EndpointInterface;
use App\Services\Endpoints\ApprovePullRequest;
use App\Services\Endpoints\Assign;
use App\Services\Endpoints\MergePullRequest;
use App\Services\Endpoints\NeedReview;
use App\Services\Endpoints\Release;
use App\Services\Endpoints\RemoveAssign;
use App\Services\Endpoints\RequestChanges;
use App\Services\GithubService;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Helper\StringHelper;

/**
 * @Bean()
 */
class CommandManager
{
    /**
     * @Inject()
     * @var GithubService
     */
    protected $githubService;

    public $commands = [
        '/approve',
        '/request-changes',
        '/merge',
        '/close',
        '/distribute',
        '/retest',
        '/assign',
        '/need-review',
        '/remove-assign',
        '/release',
    ];

    public function execute(string $command, array $target): void
    {
        $endPoint = null;
        $explodedCommand = explode(' ', $command);
        $prefix = $explodedCommand[0] ?? '';
        $repository = $target['repository']['full_name'];
        $pullRequestId = $this->parseIssueId($target);
        switch ($prefix) {
            case '/merge':
                $endPoint = new MergePullRequest($repository, $pullRequestId);
                break;
            case '/request-changes':
            case '/request_changes':
            case '/requestchanges':
                $body = $this->parseBody($explodedCommand);
                $endPoint = new RequestChanges($repository, $pullRequestId, $body);
                break;
            case '/approve':
                $endPoint = new ApprovePullRequest($repository, $pullRequestId, '');
                break;
            case '/assign':
                $body = $this->parseBody($explodedCommand);
                $endPoint = new Assign($repository, $pullRequestId, $body);
                break;
            case '/remove-assign':
            case '/remove_assign':
            case '/removeassign':
                $body = $this->parseBody($explodedCommand);
                $endPoint = new RemoveAssign($repository, $pullRequestId, $body);
                break;
            case '/need-review':
            case '/need_review':
            case '/needreview':
                $body = $this->parseBody($explodedCommand);
                $endPoint = new NeedReview($repository, $pullRequestId, $body);
                break;
            case '/release':
                $body = $this->parseBody($explodedCommand);
                $endPoint = new Release($repository, $pullRequestId, $body);
                break;
        }
        $endPoint instanceof EndpointInterface && $this->githubService->execute($endPoint);
    }

    public function isValidCommand(string $command): bool
    {
        if (! StringHelper::startsWith($command, $this->commands)) {
            return false;
        }

        switch ($command) {
            case 'assign':
            case 'need-review':
                break;
        }
        return true;
    }

    /**
     * @param array $target
     * @return mixed
     */
    private function parseIssueId(array $target)
    {
        if (isset($target['issue'])) {
            $pullRequestId = $target['issue']['number'];
        } else {
            $pullRequestId = $target['pull_request']['number'];
        }
        return $pullRequestId;
    }

    /**
     * @param $explodedCommand
     * @return string
     */
    private function parseBody($explodedCommand): string
    {
        unset($explodedCommand[0]);
        $body = implode(' ', $explodedCommand);
        return $body;
    }
}
