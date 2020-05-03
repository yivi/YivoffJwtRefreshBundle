<?php declare(strict_types=1);

namespace Yivoff\JwtTokenRefresh\Model;

use DateTimeImmutable;
use Yivoff\JwtTokenRefresh\Contracts\RefreshTokenInterface;

class RefreshToken implements RefreshTokenInterface
{

    private string            $username;
    private string            $identifier;
    private string            $verifier;
    private DateTimeImmutable $validUntil;

    public function __construct(string $username, string $identifier, string $verifier, DateTimeImmutable $validUntil)
    {
        $this->username   = $username;
        $this->identifier = $identifier;
        $this->verifier   = $verifier;
        $this->validUntil = $validUntil;
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
