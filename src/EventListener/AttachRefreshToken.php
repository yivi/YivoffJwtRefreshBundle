<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\EventListener;

use DateInterval;
use DateTimeImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;
use Yivoff\JwtRefreshBundle\Contracts\HasherInterface;
use Yivoff\JwtRefreshBundle\Contracts\RefreshTokenProviderInterface;
use Yivoff\JwtRefreshBundle\Contracts\TokenIdGeneratorInterface;
use Yivoff\JwtRefreshBundle\Model\RefreshToken;

final readonly class AttachRefreshToken
{
    public function __construct(
        private HasherInterface               $hasher,
        private TokenIdGeneratorInterface     $tokenIdGenerator,
        private string                        $parameterName,
        private int                           $tokenShelfLife,
        private RefreshTokenProviderInterface $refreshTokenProvider
    ) {}

    public function __invoke(AuthenticationSuccessEvent $event): void
    {

        $data = $event->getData();
        $user = $event->getUser();

        $userId = $user->getUserIdentifier();

        $tokenId  = $this->tokenIdGenerator->generateIdentifier(20);
        $verifier = $this->tokenIdGenerator->generateVerifier(32);

        $token = new RefreshToken(
            $userId,
            $tokenId,
            $this->hasher->hash($verifier),
            (new DateTimeImmutable())->add(new DateInterval('PT'.$this->tokenShelfLife.'S'))
        );
        $this->refreshTokenProvider->add($token);

        $data[$this->parameterName] = $token->getIdentifier().':'.$verifier;
        $event->setData($data);
    }
}
