<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */

namespace App\EventHandler;

use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use function in_array;

class IssueCommentHandler extends AbstractHandler
{
    /**
     * @Inject()
     * @var CommandManager
     */
    protected $commandManager;

    /**
     * @Inject()
     * @var LoggerInterface
     */
    protected $logger;

    public function handle(RequestInterface $request)
    {
        if (! $request instanceof \Hyperf\HttpServer\Contract\RequestInterface) {
            return;
        }
        $this->logger->debug('Receive a issue comment request.');
        $issue = $request->input(null, []);
        $comment = $request->input('comment.body', []);
        if (! $issue || ! $comment) {
            $message = 'Invalid argument.';
            $this->logger->debug($message);
            return $this->response()->withStatus(400, $message);
        }
        if (! $this->isValidUser($issue)) {
            $message = 'Invalid user operation.';
            $this->logger->debug($message);
            return $this->response()->withStatus(401, $message);
        }
        $commands = $this->parseCommands($comment);
        if (! $commands) {
            $this->logger->debug('Receive a request, but no command.');
        }
        foreach ($commands as $command) {
            $this->commandManager->execute($command, $issue);
        }
        return $this->response()->withStatus(200);
    }

    protected function parseCommands(string $body): array
    {
        $commands = [];
        $delimiter = "\r\n";
        $comments = explode($delimiter, $body);
        foreach ($comments as $comment) {
            if ($this->commandManager->isValidCommand($comment)) {
                $commands[] = $comment;
            }
        }
        return $commands;
    }

    protected function isValidUser(array $issue): bool
    {
        return isset($issue['comment']['author_association']) && in_array($issue['comment']['author_association'], [
                'MEMBER',
                'OWNER'
            ], true);
    }
}
