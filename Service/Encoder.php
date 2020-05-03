<?php declare(strict_types=1);

namespace Yivoff\JwtTokenRefresh\Service;

use Yivoff\JwtTokenRefresh\Contracts\EncoderInterface;

class Encoder implements EncoderInterface
{

    private string $hash_key;

    public function __construct(string $hash_key)
    {
        $this->hash_key = $hash_key;
    }

    public function verify(string $token, string $hash): bool
    {
        return $hash === $this->encode($token);
    }

    public function encode(string $token): string
    {
        return hash_hmac('sha256', $token, $this->hash_key);
    }
}
