<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\EventHandlers;

use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Http\Message\Server\Request;

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

    public function handle(Request $request)
    {
        $issue = $request->json(null, []);
        $comment = $request->json('comment.body', []);
        if (! $issue || ! $comment) {
            return response()->withStatus(400, 'Invalid argument.');
        }
        $commands = $this->parseCommands($comment);
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
