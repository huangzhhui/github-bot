<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */

namespace App\Utils;

class GithubUrlBuilder
{
    public static function buildIssueUrl(string $repo, int $issue): string
    {
        return '/repos/' . $repo . '/issues/' . $issue;
    }

    public static function buildPullRequestUrl(string $repo, int $issue): string
    {
        return '/repos/' . $repo . '/pulls/' . $issue;
    }

    public static function buildAssigneesUrl(string $repo, int $issue): string
    {
        return '/repos/' . $repo . '/issues/' . $issue . '/assignees';
    }

    public static function buildReviewRequestUrl(string $repo, int $issue): string
    {
        return '/repos/' . $repo . '/pulls/' . $issue . '/requested_reviewers';
    }

    public static function buildReviewsUrl(string $repo, int $issue): string
    {
        return '/repos/' . $repo . '/pulls/' . $issue . '/reviews';
    }

    public static function buildReleasesUrl(string $repo): string
    {
        return '/repos/' . $repo . '/releases';
    }

}
