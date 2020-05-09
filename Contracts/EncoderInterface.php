<?php declare(strict_types=1);

namespace Yivoff\Bundle\JwtRefresh\Contracts;

interface EncoderInterface
{

    public function verify(string $token, string $hash): bool;

    public function encode(string $token): string;
}
