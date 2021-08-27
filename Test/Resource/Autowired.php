<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Test\Resource;

use Yivoff\JwtRefreshBundle\Contracts\HasherInterface;
use Yivoff\JwtRefreshBundle\Contracts\TokenIdGeneratorInterface;
use Yivoff\JwtRefreshBundle\EventListener\AttachRefreshToken;
use Yivoff\JwtRefreshBundle\Security\Authenticator;

class Autowired
{
    private HasherInterface $encoder;
    private TokenIdGeneratorInterface $generator;
    private Authenticator $authenticator;
    private AttachRefreshToken $eventListener;

    public function __construct(
        HasherInterface $encoder,
        TokenIdGeneratorInterface $generator,
        Authenticator $authenticator,
        AttachRefreshToken $eventListener
    ) {
        $this->encoder       = $encoder;
        $this->generator     = $generator;
        $this->authenticator = $authenticator;
        $this->eventListener = $eventListener;
    }

    public function getEncoder(): HasherInterface
    {
        return $this->encoder;
    }

    public function getGenerator(): TokenIdGeneratorInterface
    {
        return $this->generator;
    }

    public function getAuthenticator(): Authenticator
    {
        return $this->authenticator;
    }

    public function getEventListener(): AttachRefreshToken
    {
        return $this->eventListener;
    }
}
