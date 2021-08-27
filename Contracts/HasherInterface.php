<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Contracts;

interface HasherInterface
{
    public function verify(string $token, string $hash): bool;

    public function hash(string $token): string;
}
