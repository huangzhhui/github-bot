<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */

namespace App\Services\Endpoints;

use App\Utils\GithubClientBuilder;
use Swoft\Helper\StringHelper;
use function in_array;

/**
 * This command is use to distribute swoft component, specified to swoft, not for everyone.
 * Notice that this command will BLOCKED the application.
 * Workflows:
 * Pull swoft-component
 * -> Parse the repositories that should be distributed
 * -> Subtree push to component repositories
 */
class Distribute extends AbstractEnpoint
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

    /**
     * @var string
     */
    protected $body;

    public function __construct(string $repository, int $pullRequestId, string $body, array $target)
    {
        $this->repository = $repository;
        $this->pullRequestId = $pullRequestId;
        $this->body = $body;
        $this->target = $target;
    }

    public function __invoke()
    {
        $this->cloneAndResetRepository($this->repository);
        $components = $this->parseTargetRepositories($this->target);
        $this->distribute($components);
    }

    private function cloneAndResetRepository(string $repository): void
    {
        if (! in_array($repository, config('github.distribute.repositories', []), true)) {
            return;
        }
        $dir = alias('@runtime/repositries');
        if (! file_exists($dir) && ! mkdir($dir, 0777, true) && ! is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
        $repositoryDir = $dir . '/' . $repository;
        if (! file_exists($repositoryDir)) {
            $command = 'cd dir && git clone git@github.com:repository.git targetDir';
            $command = str_replace('dir', $dir, $command);
            $command = str_replace('repository', $repository, $command);
            $command = str_replace('targetDir', $repository, $command);
            $output = [];
            echo $command . PHP_EOL;
            exec($command, $output);
        }
        if (file_exists($repositoryDir)) {
            $command = 'cd dir && git pull --no-edit';
            $command = str_replace('dir', $repositoryDir, $command);
            $output = [];
            echo $command . PHP_EOL;
            exec($command, $output);
        }
    }

    private function parseTargetRepositories(array $target): array
    {
        if (! isset($target['issue']['number'])) {
            return [];
        }
        $diffUrl = sprintf('/raw/swoft-cloud/swoft-component/pull/%d.diff', $target['issue']['number']);
        $response = GithubClientBuilder::create('https://patch-diff.githubusercontent.com')
            ->get($diffUrl, ['_options' => ['timeout' => 30]])
            ->getResponse();
        $modifiedComponent = [];
        $diff = explode(PHP_EOL, $response->getBody()->getContents());
        $mappings = config('github.distribute.distribute_mapping', []);
        foreach ($diff ?? [] as $line) {
            if (! StringHelper::startsWith($line, ['+++', '---'])) {
                continue;
            }
            $path = substr($line, 4);
            if (! StringHelper::startsWith($path, ['a/', 'b/'])) {
                continue;
            }
            $path = substr($path, 2);
            $explodedPath = explode('/', $path);
            if (! $explodedPath || ! isset($explodedPath[1])) {
                continue;
            }
            $dir = implode('/', [$explodedPath[0], $explodedPath[1]]);
            if (! isset($mappings[$dir])) {
                continue;
            }
            $modifiedComponent[$dir] = $mappings[$dir] ?? 0;
        }
        return $modifiedComponent;
    }

    private function distribute(array $components): void
    {
        foreach ($components as $dir => $repository) {
            if ($repository) {
                $command = 'git subtree push --prefix=%s git@github.com:%s.git master --squash';
                $command = sprintf($command, $dir, $repository);
                echo $command . PHP_EOL;
                exec($command);
            }
        }
    }
}
