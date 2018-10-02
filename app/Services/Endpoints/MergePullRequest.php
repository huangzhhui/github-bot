<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Services\Endpoints;

use App\Utils\GithubUrlBuilder;
use Swoole\Coroutine;

class MergePullRequest extends AbstractEnpoint
{
    /**
     * @var int
     */
    protected $pullRequestId;

    /**
     * @var string
     */
    protected $repository;

    public function __construct(string $repository, int $pullRequestId)
    {
        $this->repository = $repository;
        $this->pullRequestId = $pullRequestId;
    }

    public function __invoke()
    {
        $this->addApprovedComment($this->repository, $this->pullRequestId);
        $mergeUrl = GithubUrlBuilder::buildPullRequestUrl($this->repository, $this->pullRequestId) . '/merge';
        $response = $this->getClient()->put($mergeUrl, [
            'json' => [
                'merge_method' => config('github.merge.method', 'squash'),
            ]
        ])->getResponse();
        if ($response->getStatusCode() !== 200) {
            // Add a comment to notice the member the merge operation failure.
            Coroutine::sleep(10);
            $this->addComment('( Ĭ ^ Ĭ ) Merge the pull request failed, please help me ~~~');
        }
    }
}
