<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Contracts;

interface RefreshTokenProviderInterface
{
    public function getTokenWithIdentifier(string $identifier): ?RefreshTokenInterface;

    public function deleteTokenWithIdentifier(string $identifier): void;

    public function add(RefreshTokenInterface $refreshToken): void;

    public function getTokenForUsername(string $username): ?RefreshTokenInterface;
}
