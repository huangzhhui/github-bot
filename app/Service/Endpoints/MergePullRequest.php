<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Service\Endpoints;

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

    /**
     * @var array
     */
    protected $target;

    public function __construct(string $repository, int $pullRequestId, array $target)
    {
        $this->repository = $repository;
        $this->pullRequestId = $pullRequestId;
        $this->target = $target;
    }

    public function __invoke()
    {
        $this->addApprovedComment($this->repository, $this->pullRequestId);
        $mergeUrl = GithubUrlBuilder::buildPullRequestUrl($this->repository, $this->pullRequestId) . '/merge';
        $params = [
            'merge_method' => config('github.merge.method', 'squash'),
        ];
        $pullRequestTitle = value(function () {
            if (isset($this->target['issue']['title'])) {
                return str_replace([
                    'title', 'url'
                ], [
                    $this->target['issue']['title'],
                    $this->target['issue']['pull_request']['html_url'],
                ], 'title (url)');
            }
            return '';
        });
        $pullRequestTitle && $params['commit_title'] = $pullRequestTitle;
        $response = $this->getClient()->put($mergeUrl, [
            'json' => $params
        ])->getResponse();
        if ($response->getStatusCode() !== 200) {
            // Add a comment to notice the member the merge operation failure.
            Coroutine::sleep(10);
            $this->addComment('( Ĭ ^ Ĭ ) Merge the pull request failed, please help me ~~~');
            echo $response->getStatusCode() . ':' . $response->getBody()->getContents() . PHP_EOL;
        }
    }
}
