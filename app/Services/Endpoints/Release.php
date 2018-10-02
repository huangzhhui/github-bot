<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */

namespace App\Services\Endpoints;

use App\Utils\GithubUrlBuilder;
use function in_array;

class Release extends AbstractEnpoint
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

    /**
     * @var string
     */
    protected $version;

    /**
     * @var array
     */
    protected $latestVersion;

    public function __construct(string $repository, int $pullRequestId, string $body = '')
    {
        $this->repository = $repository;
        $this->pullRequestId = $pullRequestId;
        [$project, $version] = explode(' ', $body);
        $this->project = value(function () use ($project, $body) {
            if (in_array($project, ['self', 'current'])) {
                return $body['repository']['full_name'];
            }
            return $project;
        });
        $this->version = $version;
    }

    public function __invoke()
    {
        $lastestVersion = $this->getLatestReleaseVersion();
        $uri = GithubUrlBuilder::buildReleasesUrl($this->repository);
        $tagName = $this->getTagName($this->version);
        $releaseName = 'v' . $tagName;
        $releaseContent = $this->getReleaseContent();
        $prerelease = $lastestVersion['prerelease'] ?? false;
        $this->getClient()->post($uri, [
            'json' => [
                'tag_name' => $tagName,
                'name' => $releaseName,
                'body' => $releaseContent,
                'prerelease' => $prerelease,
            ],
        ]);
    }

    protected function getLatestReleaseVersion(): array
    {
        if (! $this->latestVersion) {
            $uri = GithubUrlBuilder::buildReleasesUrl($this->repository) . '/latest';
            $response = $this->getClient()->get($uri)->getResponse();
            $content = $response->getBody()->getContents();
            $this->latestVersion = json_decode($content, true);
        }
        return (array)$this->latestVersion;
    }

    protected function getReleaseContent(): string
    {

    }

    private function getTagName($version): string
    {
        return $version ? $this->getLatestReleaseVersion()['tag_name'] : '0.0.1';
    }
}
