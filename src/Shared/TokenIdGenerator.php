<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Shared;

use Yivoff\JwtRefreshBundle\Contracts\TokenIdGeneratorInterface;
use function str_repeat;
use function str_shuffle;
use function substr;

final class TokenIdGenerator implements TokenIdGeneratorInterface
{
    private string $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';

    public function generateIdentifier(int $length): string
    {
        return $this->randomString($length);
    }

    public function generateVerifier(int $length): string
    {
        return $this->randomString($length);
    }

    private function randomString(int $length): string
    {
        return substr(str_shuffle(str_repeat($this->chars, $length)), 0, $length);
    }
}
