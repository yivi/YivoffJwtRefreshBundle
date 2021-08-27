<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Model;

use DateTimeImmutable;
use Yivoff\JwtRefreshBundle\Contracts\RefreshTokenInterface;

class RefreshToken implements RefreshTokenInterface
{
    public function __construct(private string $username, private string $identifier, private string $verifier, private DateTimeImmutable $validUntil)
    {
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getVerifier(): string
    {
        return $this->verifier;
    }

    public function getValidUntil(): int
    {
        return $this->validUntil->getTimestamp();
    }
}
