<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Contracts;

interface RefreshTokenInterface
{
    public function getUsername(): string;

    public function getIdentifier(): string;

    public function getVerifier(): string;

    public function getValidUntil(): int;
}
