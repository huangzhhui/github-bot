<?php
/**
 * @contact huangzhwork@gmail.com
 * @license https://github.com/huangzhhui/github-bot/blob/master/LICENSE
 */
namespace App\Services;

use App\Enums\Github;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Value;
use Swoft\Http\Message\Server\Request;

/**
 * Github Webhoot Signature Service
 * @Bean()
 */
class SignatureService
{
    /**
     * @Value(name="${config.github.webhook.secret}")
     * @var string
     */
    protected $secret = '';

    public function isValid(Request $request): bool
    {
        [$algo, $hash] = explode('=', $request->getHeaderLine(GithubService::HEADER_SIGNATURE), 2);
        $payloadHash = hash_hmac($algo, $request->raw(''), $this->getSecret());
        return $payloadHash === $hash;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }
}
