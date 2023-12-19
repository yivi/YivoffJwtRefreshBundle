<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Event;

use Yivoff\JwtRefreshBundle\Exception\FailType;

class JwtRefreshTokenFailed
{
    public function __construct(public readonly FailType $failType, public readonly ?string $tokenId, public readonly ?string $userIdentifier) {}
}
