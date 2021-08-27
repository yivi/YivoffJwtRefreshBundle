<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Test\Resource;

use Safe\DateTimeImmutable;
use Yivoff\JwtRefreshBundle\Contracts\PurgableRefreshTokenProviderInterface;

class PurgableInMemoryRefreshTokenProvider extends InMemoryRefreshTokenProvider implements PurgableRefreshTokenProviderInterface
{

    public function purgeExpiredTokens(): void
    {
        foreach ($this->tokens as $tokenId => $token) {
            if ($token->getValidUntil() < (new DateTimeImmutable())->getTimestamp()) {
                unset($this->tokens[$tokenId]);
            }
        }
    }
}
