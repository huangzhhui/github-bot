<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */

namespace App\EventHandler;

use App\Service\EndpointInterface;
use App\Service\Endpoints;
use App\Service\GithubService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Str;
use Psr\Log\LoggerInterface;

class CommandManager
{
    /**
     * @Inject()
     * @var GithubService
     */
    protected $githubService;

    /**
     * @Inject()
     * @var LoggerInterface
     */
    protected $logger;

    public $commands = [
        '/approve',
        '/request-changes',
        '/merge',
        '/close',
        '/retest',
        '/assign',
        '/need-review',
        '/remove-assign',
        '/release',
        '/log',
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
                $endPoint = new Endpoints\MergePullRequest($repository, $pullRequestId, $target);
                break;
            case '/request-changes':
            case '/request_changes':
            case '/requestchanges':
                $body = $this->parseBody($explodedCommand);
                $endPoint = new Endpoints\RequestChanges($repository, $pullRequestId, $body);
                break;
            case '/approve':
                $endPoint = new Endpoints\ApprovePullRequest($repository, $pullRequestId, '');
                break;
            case '/assign':
                $body = $this->parseBody($explodedCommand);
                $endPoint = new Endpoints\Assign($repository, $pullRequestId, $body);
                break;
            case '/remove-assign':
            case '/remove_assign':
            case '/removeassign':
                $body = $this->parseBody($explodedCommand);
                $endPoint = new Endpoints\RemoveAssign($repository, $pullRequestId, $body);
                break;
            case '/need-review':
            case '/need_review':
            case '/needreview':
                $body = $this->parseBody($explodedCommand);
                $endPoint = new Endpoints\NeedReview($repository, $pullRequestId, $body);
                break;
            case '/log':
                $endPoint = new Endpoints\Log($target);
                break;
        }
        if ($endPoint instanceof EndpointInterface) {
            $this->logger->debug(sprintf('Trigger command: %s', $prefix));
            $this->githubService->execute($endPoint);
        }
    }

    public function isValidCommand(string $command): bool
    {
        if (! Str::startsWith($command, $this->commands)) {
            return false;
        }
        return true;
    }

    /**
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

    private function parseBody(array $explodedCommand): string
    {
        unset($explodedCommand[0]);
        return implode(' ', $explodedCommand);
    }
}
