<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Shared;

use Yivoff\JwtRefreshBundle\Contracts\HasherInterface;
use function hash_equals;
use function hash_hmac;

/**
 * @internal
 */
final class Hasher implements HasherInterface
{
    public function __construct(private string $hash_key)
    {
    }

    public function verify(string $token, string $hash): bool
    {
        return hash_equals($hash, $this->hash($token));
    }

    public function hash(string $token): string
    {
        return hash_hmac('sha256', $token, $this->hash_key);
    }
}
