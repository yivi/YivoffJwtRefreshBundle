<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Contracts;

interface PurgableRefreshTokenProviderInterface
{
    public function purgeExpiredTokens(): void;
}
