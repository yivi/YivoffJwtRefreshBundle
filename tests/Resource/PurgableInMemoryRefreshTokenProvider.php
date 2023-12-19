<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Test\Resource;

use DateTimeImmutable;
use Yivoff\JwtRefreshBundle\Contracts\PurgableRefreshTokenProviderInterface;

class PurgableInMemoryRefreshTokenProvider extends InMemoryRefreshTokenProvider implements PurgableRefreshTokenProviderInterface
{
    public function purgeExpiredTokens(): void
    {
        foreach ($this->tokens as $tokenId => $token) {
            $now        = (new DateTimeImmutable())->getTimestamp();
            $expiration = $token->getValidUntil();
            if ($expiration < $now) {
                unset($this->tokens[$tokenId]);
            }
        }
    }
}
