<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */

namespace App\EventHandlers;

use Psr\Log\LoggerInterface;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Http\Message\Server\Request;
use function in_array;

/**
 * @Bean()
 */
class IssueCommentHandler extends AbstractHandler
{
    /**
     * @Inject()
     * @var CommandManager
     */
    protected $commandManager;

    /**
     * @Inject("logger")
     * @var LoggerInterface
     */
    protected $logger;

    public function handle(Request $request)
    {
        $this->logger->debug('Receive a request.');
        $issue = $request->json(null, []);
        $comment = $request->json('comment.body', []);
        if (! $issue || ! $comment) {
            $message = 'Invalid argument.';
            $this->logger->debug($message);
            return response()->withStatus(400, $message);
        }
        if (! $this->isValidUser($issue)) {
            $message = 'Invalid user operation.';
            $this->logger->debug($message);
            return response()->withStatus(401, $message);
        }
        $commands = $this->parseCommands($comment);
        if (! $commands) {
            $this->logger->debug('Receive a request, but no command.');
        }
        foreach ($commands as $command) {
            $this->commandManager->execute($command, $issue);
        }
        return response()->withStatus(200);
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
