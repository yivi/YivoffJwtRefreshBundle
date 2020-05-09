<?php declare(strict_types=1);

namespace Yivoff\JwtRefresh\EventListener;

use DateInterval;
use DateTimeImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;
use Yivoff\JwtRefresh\Contracts\EncoderInterface;
use Yivoff\JwtRefresh\Contracts\IdGeneratorInterface;
use Yivoff\JwtRefresh\Contracts\RefreshTokenProviderInterface;
use Yivoff\JwtRefresh\Model\RefreshToken;

final class AttachRefreshToken
{

    private RefreshTokenProviderInterface  $tokenProvider;
    private EncoderInterface $encoder;
    private IdGeneratorInterface $idGenerator;

    private string $parameterName;
    private int    $tokenShelfLife;

    public function __construct(
        EncoderInterface $encoder,
        IdGeneratorInterface $idGenerator,
        string $parameterName,
        int $tokenShelfLife,
        RefreshTokenProviderInterface $tokenProvider
    ) {
        $this->tokenProvider  = $tokenProvider;
        $this->encoder        = $encoder;
        $this->idGenerator    = $idGenerator;
        $this->parameterName  = $parameterName;
        $this->tokenShelfLife = $tokenShelfLife;
    }

    public function __invoke(AuthenticationSuccessEvent $event)
    {
        /** @var UserInterface $user */
        $data = $event->getData();
        $user = $event->getUser();

        if (null !== $oldToken = $this->tokenProvider->getTokenForUsername($user->getUsername())) {
            $this->tokenProvider->deleteTokenWithIdentifier($oldToken->getIdentifier());
        }

        $identifier   = $this->idGenerator->generateIdentifier();
        $raw_verifier = $this->idGenerator->generateVerifier();

        $token = new RefreshToken(
            $user->getUsername(),
            $identifier,
            $this->encoder->encode($raw_verifier),
            (new DateTimeImmutable())->add(new DateInterval('PT' . $this->tokenShelfLife . 'S'))
        );
        $this->tokenProvider->add($token);

        $data[$this->parameterName] = $token->getIdentifier() . ':' . $raw_verifier;
        $event->setData($data);
    }
}
