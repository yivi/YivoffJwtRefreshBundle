<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Test\Unit;

use Yivoff\JwtRefreshBundle\Contracts\HasherInterface;
use Yivoff\JwtRefreshBundle\Contracts\TokenIdGeneratorInterface;

use function random_int;
use function str_repeat;

trait MockerTrait
{
    private function createHasher(bool $success = true): HasherInterface
    {
        return new class($success) implements HasherInterface {
            public function __construct(private bool $success) {}

            public function hash(string $token): string
            {
                return $token;
            }

            public function verify(string $token, string $hash): bool
            {
                return $this->success;
            }
        };
    }

    private function createDummyIdGenerator(): TokenIdGeneratorInterface
    {
        return new class() implements TokenIdGeneratorInterface {
            public function generateIdentifier(int $length): string
            {
                return random_int(10, 99).str_repeat('a', $length - 2);
            }

            public function generateVerifier(int $length): string
            {
                return random_int(10, 99).str_repeat('a', $length - 2);
            }
        };
    }
}
