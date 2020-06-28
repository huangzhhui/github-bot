<?php

declare(strict_types=1);
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Service;

use Hyperf\Config\Annotation\Value;
use Psr\Http\Message\RequestInterface;

/**
 * Github Webhoot Signature Service.
 */
class SignatureService
{
    /**
     * @Value("github.webhook.secret")
     * @var string
     */
    protected $secret = '';

    public function isValid(RequestInterface $request): bool
    {
        [$algo, $hash] = explode('=', $request->getHeaderLine(GithubService::HEADER_SIGNATURE), 2);
        $payloadHash = hash_hmac($algo, $request->getBody()->getContents() ?? '', $this->getSecret());
        return $payloadHash === $hash;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }
}
