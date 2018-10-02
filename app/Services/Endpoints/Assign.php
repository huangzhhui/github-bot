<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Services\Endpoints;

use App\Utils\GithubUrlBuilder;
use Swoole\Coroutine;

class Assign extends AbstractEnpoint
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
        $client = $this->getClient();
        $assigneesUrl = GithubUrlBuilder::buildAssigneesUrl($this->repository, $this->issueId);
        $assignUsers = $this->parseTargetUsers();
        if (! $assignUsers) {
            return;
        }
        $response = $client->post($assigneesUrl, [
            'json' => [
                'assignees' => $assignUsers,
            ],
        ])->getResponse();
        if ($response->getStatusCode() !== 201) {
            Coroutine::sleep(10);
            $this->addSorryComment();
        }
    }

    private function addSorryComment(): void
    {
        $this->addComment('( Ĭ ^ Ĭ ) Assign failed, sorry ~~~');
    }
}
