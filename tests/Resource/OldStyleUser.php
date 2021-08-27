<?php declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Test\Resource;

use Symfony\Component\Security\Core\User\UserInterface;

class OldStyleUser implements UserInterface
{

    public function __construct(private string $username, private ?string $password = null)
    {
    }

    public function getRoles(): array
    {
        return [];
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }

    public function getUsername(): string
    {
        return $this->username;
    }


}
