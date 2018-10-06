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

/**
 * @Bean()
 */
class PullRequestReviewHandler extends AbstractHandler
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
        $this->logger->debug('Receive a pull request review request.');
        $issue = $request->json(null, []);
        $comment = $request->json('review.body', []);
        if (! $issue || ! $comment) {
            $message = 'Invalid argument.';
            $this->logger->debug($message);
            return response()->withStatus(400, $message);
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
}
