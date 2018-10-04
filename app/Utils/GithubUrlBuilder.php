<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */

namespace App\Utils;

class GithubUrlBuilder
{

    public static function buildRepositoryUrl(string $repository): string
    {
        return '/repos/' . $repository;
    }

    public static function buildTagsUrl(string $repository): string
    {
        return self::buildRepositoryUrl($repository) . '/git/refs/tags';
    }

    public static function buildTagBUrl(string $repository, string $tagSha): string
    {
        return self::buildRepositoryUrl($repository) . '/git/tags/' . $tagSha;
    }

    public static function buildIssueUrl(string $repository, int $issue): string
    {
        return self::buildRepositoryUrl($repository) . '/issues/' . $issue;
    }

    public static function buildCommitsUrl(string $repository): string
    {
        return self::buildRepositoryUrl($repository) . '/commits';
    }

    public static function buildPullsUrl(string $repository): string
    {
        return self::buildRepositoryUrl($repository) . '/pulls';
    }

    public static function buildReleasesUrl(string $repository): string
    {
        return self::buildRepositoryUrl($repository) . '/releases';
    }

    public static function buildPullRequestUrl(string $repository, int $pullRequestId): string
    {
        return self::buildPullsUrl($repository) . '/' . $pullRequestId;
    }

    public static function buildAssigneesUrl(string $repository, int $issue): string
    {
        return self::buildRepositoryUrl($repository) . '/issues/' . $issue . '/assignees';
    }

    public static function buildReviewRequestUrl(string $repository, int $issue): string
    {
        return self::buildRepositoryUrl($repository) . '/pulls/' . $issue . '/requested_reviewers';
    }

    public static function buildReviewsUrl(string $repository, int $issue): string
    {
        return self::buildRepositoryUrl($repository) . '/pulls/' . $issue . '/reviews';
    }

}
