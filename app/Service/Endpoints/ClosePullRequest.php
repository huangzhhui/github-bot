<?php

namespace App\Service\Endpoints;


use Hyperf\HttpServer\Contract\RequestInterface;

class ClosePullRequest extends AbstractEnpoint
{

    /**
     * @var \Hyperf\HttpServer\Contract\RequestInterface
     */
    protected $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function __invoke()
    {
        $repository = $this->request->input('repository.full_name', '');
        if (! $this->isHyperfComponentRepo($repository)) {
            // Should not close this PR automatically.
            return $this->response()->withStatus(200);
        }
        $pullRequestId = $this->request->input('number', 0);
        $currentState = $this->request->input('pull_request.state', '');
        $senderName = $this->request->input('sender.login', '');
        try {
            retry(3, function () use ($repository, $pullRequestId, $currentState, $senderName) {
                if ($currentState === 'closed') {
                    return;
                }
                $commentResult = $this->addClosedComment($repository, $pullRequestId, $senderName);
                if ($commentResult) {
                    $this->logger->info(sprintf('Pull Request %s#%d added auto comment.', $repository, $pullRequestId));
                } else {
                    $this->logger->warning(sprintf('Pull Request %s#%d add auto comment failed.', $repository, $pullRequestId));
                }
                $closeResult = $this->closePullRequest($repository, $pullRequestId, $currentState);
                if ($closeResult) {
                    $this->logger->info(sprintf('Pull Request %s#%d has been closed.', $repository, $pullRequestId));
                } else {
                    $this->logger->warning(sprintf('Pull Request %s#%d close failed.', $repository, $pullRequestId));
                }
            }, 5);
        } catch (Throwable $e) {
            // Do nothing
        }
    }
}