<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Service\Endpoints;

use a;
use App\Utils\GithubUrlBuilder;
use Swoole\Coroutine;

class SwitchTo extends AbstractEnpoint
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
        $issueUrl = GithubUrlBuilder::buildIssueUrl($this->repository, $this->issueId);
        $type = $this->parseType();
        if (! $type) {
            return;
        }
        $title = $this->body['issue']['title'] ?? '';
        $labels = $this->body['labels'] ?? [];
        $changedtitle = $this->modifyTitle($title, $type);
        $changedLabels = $this->modifyLabels($labels, $type);
        $response = $client->patch($issueUrl, [
            'json' => [
                'title' => $changedtitle,
                'labels' => $changedLabels,
            ],
        ]);
        if ($response->getStatusCode() !== 200) {
            Coroutine::sleep(10);
            $this->addSorryComment();
        }
    }

    protected function modifyTitle(string $title, string $type): string
    {
        $start = strpos($title, '[');
        $end = strpos($title, ']');
        if ($start !== false && $end !== false && $end > $start) {
            $title = substr($title, $start, $end - $start);
            $title = strtoupper($type) . ' ' . $title;
        }
        return $title;
    }

    protected function addSorryComment(): void
    {
        $this->addComment('( Ĭ ^ Ĭ ) Switch failed, sorry ~~~');
    }

    protected function modifyLabels(array $labels, string $type): array
    {
        foreach ($labels as $key => $label) {
            if (! isset($label['name'])) {
                unset($labels[$key]);
            }
            $labels[$key] = $label['name'];
        }

        unset($labels['bug'], $labels['question'], $labels['enhancement']);
        switch ($type) {
            case 'bug':
            case 'question':
                $labels[] = $type;
                break;
            case 'feature':
                $labels[] = 'enhancement';
                break;
        }
        return $labels;
    }
}
