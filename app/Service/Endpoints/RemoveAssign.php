<?php

declare(strict_types=1);
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Service\Endpoints;

use App\Utils\GithubUrlBuilder;
use Swoole\Coroutine;

class RemoveAssign extends AbstractEnpoint
{
    /**
     * @var int
     */
    protected $issueId;

    /**
     * @var string
     */
    protected $repository;

    /**
     * @var string
     */
    protected $body;

    public function __construct(string $repository, int $issueId, string $body)
    {
        $this->repository = $repository;
        $this->issueId = $issueId;
        $this->body = $body;
    }

    public function __invoke()
    {
        $assigneesUrl = GithubUrlBuilder::buildAssigneesUrl($this->repository, $this->issueId);
        $assignees = $this->parseTargetUsers();
        if (! $assignees) {
            return;
        }
        $response = $this->getClient()->delete($assigneesUrl, [
            'json' => [
                'assignees' => $assignees,
            ],
        ]);
        if ($response->getStatusCode() !== 201) {
            Coroutine::sleep(10);
            $this->addSorryComment();
        }
    }

    private function addSorryComment(): void
    {
        $this->addComment('( Ĭ ^ Ĭ ) Remove the assignees failed, sorry ~~~');
    }
}
