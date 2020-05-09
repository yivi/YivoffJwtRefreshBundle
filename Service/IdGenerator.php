<?php declare(strict_types=1);

namespace Yivoff\JwtTokenRefresh\Service;

use Yivoff\Bundle\JwtRefresh\Contracts\IdGeneratorInterface;

class IdGenerator implements IdGeneratorInterface
{

    public function generateIdentifier(): string
    {
        return hash('md5', random_bytes(9));
    }

    public function generateVerifier(): string
    {
        return hash('sha256', random_bytes(12));
    }
}
