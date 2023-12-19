<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Test\Resource;

use InvalidArgumentException;
use Yivoff\JwtRefreshBundle\Contracts\RefreshTokenInterface;
use Yivoff\JwtRefreshBundle\Contracts\RefreshTokenProviderInterface;

use function array_key_exists;

class InMemoryRefreshTokenProvider implements RefreshTokenProviderInterface
{
    /** @var array<string, \Yivoff\JwtRefreshBundle\Contracts\RefreshTokenInterface> */
    protected array $tokens = [];

    public function getTokenWithIdentifier(string $identifier): ?RefreshTokenInterface
    {
        if (array_key_exists($identifier, $this->tokens)) {
            return $this->tokens[$identifier];
        }

        return null;
    }

    public function deleteTokenWithIdentifier(string $identifier): void
    {
        if (array_key_exists($identifier, $this->tokens)) {
            unset($this->tokens[$identifier]);
        }
    }

    public function add(RefreshTokenInterface $refreshToken): void
    {
        if (array_key_exists($refreshToken->getIdentifier(), $this->tokens)) {
            throw new InvalidArgumentException('Cannot add a duplicated token');
        }

        $this->tokens[$refreshToken->getIdentifier()] = $refreshToken;
    }

    public function getTokenForUsername(string $username): ?RefreshTokenInterface
    {
        foreach ($this->tokens as $token) {
            if ($token->getUsername() === $username) {
                return $token;
            }
        }

        return null;
    }
}
