<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */

namespace App\Services\Endpoints;

use App\Utils\GithubUrlBuilder;
use Psr\Http\Message\ResponseInterface;
use Swoft\Helper\StringHelper;
use function alias;
use function config;
use function count;
use function in_array;
use function is_array;
use function is_string;
use function strlen;

/**
 * This release rules based on Swoft release rules, not for everyone.
 * Workflows:
 * Get latest release version if existed
 * -> Get tag info by tag_name
 * -> Get the commit sha of tag
 * -> Filter commit logs
 * -> Build release content
 * -> release a version.
 */
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

    public function __construct(string $repository, int $pullRequestId, string $body, array $target)
    {

        $this->repository = $repository;
        $this->pullRequestId = $pullRequestId;

        [$project, $version] = value(function () use ($body) {
            $result = explode(' ', $body, 2);
            return [
                isset($result[0]) && $result[0] ? $result[0] : 'self',
                isset($result[1]) && $result[1] ? $result[1] : 'step'
            ];
        });
        $this->project = value(function () use ($project) {
            if (in_array($project, ['self', 'current'])) {
                $project = $this->repository;
            }
            return $project;
        });
        $this->version = $version;
    }

    public function __invoke()
    {
        $this->project = $this->getProject($this->project);
        $latestVersionRelease = $this->getLatestReleaseVersion();
        if (! isset($latestVersionRelease['tag_name'])) {
            return;
        }
        $tag = $this->getTag($latestVersionRelease['tag_name']);
        $latestVersionTagSha = $tag['object']['sha'] ?? '';
        if (! $latestVersionTagSha) {
            return;
        }
        $commits = $this->getUnReleaseCommits($latestVersionTagSha);
        $releaseContent = $this->buildReleaseContent($commits);
        $uri = GithubUrlBuilder::buildReleasesUrl($this->project);
        [$version, $latestVersion] = $this->getReleaseVersion($this->version, $latestVersionRelease['tag_name']);
        $tagName = str_replace($latestVersion, $version, $latestVersionRelease['tag_name']);
        $releaseName = str_replace($latestVersion, $version, $latestVersionRelease['name']);
        $prerelease = $latestVersionRelease['prerelease'] ?? false;
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
            // Because the draft releases and prereleases are not returned by this endpoint,
            // So the bot will get all releases and find the latest releases, including prereleases.
            $url = GithubUrlBuilder::buildReleasesUrl($this->project);
            $response = $this->getClient()->get($url)->getResponse();
            $content = $response->getBody()->getContents();
            $releases = json_decode($content, true);
            foreach ($releases ?? [] as $release) {
                if (! $this->latestVersion) {
                    $this->latestVersion = $release;
                }
                if (isset($this->latestVersion['create_time'], $release['create_time']) && $release['create_time'] > $this->latestVersion['create_time']) {
                    $this->latestVersion = $release;
                }
            }
        }
        return (array)$this->latestVersion;
    }

    protected function buildReleaseContent(array $commits): string
    {
        $template = require alias(config('github.release.message_template'));
        $cateConfig = config('github.release.message_cate', [
            'added' => [],
            'changed' => [],
            'fixed' => [],
            'deprecated' => [],
            'removed' => [],
        ]);
        $cate = [];
        foreach ($commits as $commit) {
            switch ($commit['message']) {
                case StringHelper::startsWith($commit['message'], $cateConfig['added']):
                    $cate['added'][] = '- ' . ltrim($this->replaceFirst($cateConfig['added'], '', $commit['message']));
                    break;
                case StringHelper::startsWith($commit['message'], $cateConfig['changed']):
                    $cate['changed'][] = '- ' . ltrim($this->replaceFirst($cateConfig['changed'], '', $commit['message']));
                    break;
                case StringHelper::startsWith($commit['message'], $cateConfig['fixed']):
                    $cate['fixed'][] = '- ' . ltrim($this->replaceFirst($cateConfig['fixed'], '', $commit['message']));
                    break;
                case StringHelper::startsWith($commit['message'], $cateConfig['deprecated']):
                    $cate['deprecated'][] = '- ' . ltrim($this->replaceFirst($cateConfig['deprecated'], ''), $commit['message']);
                    break;
                case StringHelper::startsWith($commit['message'], $cateConfig['removed']):
                    $cate['removed'][] = '- ' . ltrim($this->replaceFirst($cateConfig['removed'], '', $commit['message']));
                    break;
                default:
                    $cate['others'][] = $commit['message'];
                    break;
            }
        }

        $template = str_replace([
            '::added',
            '::changed',
            '::deprecated',
            '::removed',
            '::fixed',
            '::others',
        ], [
            (isset($cate['added']) && $cate['added']) ? implode("\r\n", $cate['added']) : '- Nothing',
            (isset($cate['changed']) && $cate['changed']) ? implode("\r\n", $cate['changed']) : '- Nothing',
            (isset($cate['deprecated']) && $cate['deprecated']) ? implode("\r\n", $cate['deprecated']) : '- Nothing',
            (isset($cate['removed']) && $cate['removed']) ? implode("\r\n", $cate['removed']) : '- Nothing',
            (isset($cate['fixed']) && $cate['fixed']) ? implode("\r\n", $cate['fixed']) : '- Nothing',
            (isset($cate['others']) && $cate['others']) ? implode("\r\n", $cate['others']) : '- Nothing',
        ], $template);
        return $template;
    }

    private function getReleaseVersion(string $version, string $latestReleaseTagName): array
    {
        $length = strlen($latestReleaseTagName);
        $versions = [];
        $y = 0;
        for ($i = 0; $i < $length; $i++) {
            if (isset($latestReleaseTagName[$i]) && is_numeric($latestReleaseTagName[$i])) {
                ! isset($versions[$y]) && $versions[$y] = '';
                $versions[$y] .= $latestReleaseTagName[$i];
            } elseif ($latestReleaseTagName[$i] === '.') {
                $y++;
            }
        }
        $currentVersion = implode('.', $versions);
        if ($version === 'step') {
            $versions[count($versions) - 1]++;
            $version = implode('.', $versions);
        }
        return [
            // Next version
            $version,
            // Current latest version
            $currentVersion,
        ];
    }

    private function addSorryComment(): ResponseInterface
    {
        return $this->addComment('( Ĭ ^ Ĭ ) Release failed, sorry ~~~');
    }

    private function getTag(string $tagName): array
    {
        $url = GithubUrlBuilder::buildTagsUrl($this->project);
        $response = $this->getClient()->get($url)->getResponse();
        if ($response->getStatusCode() !== 200) {
            $this->addSorryComment();
            return '';
        }
        $tags = json_decode($response->getBody()->getContents(), true);
        foreach ($tags ?? [] as $tag) {
            if (isset($tag['ref'], $tag['object']['type']) && $tag['ref'] === ('refs/tags/' . $tagName)) {
                return $tag;
            }
        }
        return [];
    }

    private function getUnReleaseCommits(string $lastestVersionTagSha): array
    {
        $url = '';
        $unReleaseCommits = [];
        $continue = true;
        while ($continue) {
            if (! $url) {
                $url = GithubUrlBuilder::buildRepositoryUrl($this->project) . '/commits';
            }
            $response = $this->getClient()->get($url)->getResponse();
            if ($response->getStatusCode() !== 200) {
                $this->addSorryComment();
                return $unReleaseCommits;
            }
            $commits = json_decode($response->getBody()->getContents(), true);
            if (! $commits) {
                $continue = false;
            }
            foreach ($commits ?? [] as $commit) {
                if (isset($commit['sha']) && $commit['sha'] === $lastestVersionTagSha) {
                    $continue = false;
                    break;
                }
                $unReleaseCommits[] = [
                    'sha' => $commit['sha'],
                    'message' => $commit['commit']['message'],
                ];
            }
            // NO break, parse the next page
            if ($response->hasHeader('link')) {
                $url = value(function () use ($response, &$continue) {
                    $links = explode(',', $response->getHeaderLine('link'));
                    foreach ($links as $link) {
                        if (StringHelper::endsWith($link, 'next"')) {
                            return trim(explode(';', $link)[0], '<>');
                        }
                    }
                    $continue = false;
                    return '';
                });
            }
        }
        return $unReleaseCommits;
    }

    private function getProject(string $project): string
    {
        $alias = config('github.release.repository_alias', []);
        return $alias[$project] ?? $project;
    }

    /**
     * @param string|array $search
     * @param string|array $replace
     * @param string $subject
     * @return string
     */
    private function replaceFirst($search, $replace, $subject)
    {
        if (is_string($search) && is_string($replace)) {
            return StringHelper::replaceFirst($search, $replace, $subject);
        } elseif (is_array($search)) {
            foreach ($search as $key => $value) {
                if (is_array($replace)) {
                    $subject = StringHelper::replaceFirst($value, $replace[$key], $subject);
                } else {
                    $subject = StringHelper::replaceFirst($value, $replace, $subject);
                }
            }
        }
        return $subject;
    }

}
