<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Yivoff\JwtRefreshBundle\Contracts\HasherInterface;
use Yivoff\JwtRefreshBundle\Contracts\RefreshTokenInterface;
use Yivoff\JwtRefreshBundle\Contracts\RefreshTokenProviderInterface;
use Yivoff\JwtRefreshBundle\Event\JwtRefreshTokenFailed;
use Yivoff\JwtRefreshBundle\Event\JwtRefreshTokenSucceeded;
use Yivoff\JwtRefreshBundle\Exception\JwtRefreshException;
use Yivoff\JwtRefreshBundle\Exception\PayloadInvalidException;
use Yivoff\JwtRefreshBundle\Exception\TokenExpiredException;
use Yivoff\JwtRefreshBundle\Exception\TokenInvalidException;
use Yivoff\JwtRefreshBundle\Exception\TokenNotFoundException;
use function explode;
use function str_contains;
use function time;

final class Authenticator extends AbstractAuthenticator
{
    public function __construct(
        private HasherInterface $encoder,
        private AuthenticationSuccessHandler $successHandler,
        private RefreshTokenProviderInterface $tokenProvider,
        private EventDispatcherInterface $eventDispatcher,
        private string $parameterName
    ) {
    }

    public function authenticate(HttpFoundation\Request $request): Passport
    {
        $credentials = (string) $request->request->get($this->parameterName);

        if (!str_contains($credentials, ':')) {
            throw new PayloadInvalidException(null, null);
        }

        [$tokenId, $userProvidedVerification] = explode(':', $credentials);

        $token = $this->tokenProvider->getTokenWithIdentifier($tokenId);

        if (!$token instanceof RefreshTokenInterface) {
            throw new TokenNotFoundException($tokenId, null);
        }

        if ($token->getValidUntil() <= time()) {
            throw new TokenExpiredException($tokenId, $token->getUsername());
        }

        if (!$this->encoder->verify($userProvidedVerification, $token->getVerifier())) {
            throw new TokenInvalidException($tokenId, $token->getUsername());
        }

        $this->eventDispatcher->dispatch(new JwtRefreshTokenSucceeded($tokenId, $token->getUsername()));

        return new SelfValidatingPassport(new UserBadge($token->getUsername()));
    }

    public function supports(HttpFoundation\Request $request): bool
    {
        return null !== $request->request->get($this->parameterName);
    }

    public function onAuthenticationFailure(HttpFoundation\Request $request, AuthenticationException $exception): HttpFoundation\Response
    {
        /** @var JwtRefreshException $exception */
        $this->eventDispatcher->dispatch(new JwtRefreshTokenFailed($exception->failType, $exception->tokenId, $exception->userIdentifier));

        return new HttpFoundation\JsonResponse(['error' => $exception->getMessage()], HttpFoundation\Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(HttpFoundation\Request $request, TokenInterface $token, string $firewallName): ?HttpFoundation\Response
    {
        return $this->successHandler->onAuthenticationSuccess($request, $token);
    }
}
