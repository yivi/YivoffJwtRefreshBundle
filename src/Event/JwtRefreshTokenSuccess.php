<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Event;

class JwtRefreshTokenSuccess
{
    public function __construct(public readonly string $tokenId, public readonly string $userIdentifier)
    {
    }
}
