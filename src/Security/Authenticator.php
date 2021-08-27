<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Yivoff\JwtRefreshBundle\Contracts\HasherInterface;
use Yivoff\JwtRefreshBundle\Contracts\RefreshTokenInterface;
use Yivoff\JwtRefreshBundle\Contracts\RefreshTokenProviderInterface;
use function explode;
use function str_contains;
use function time;

final class Authenticator extends AbstractAuthenticator
{
    public function __construct(
        private HasherInterface $encoder,
        private AuthenticationSuccessHandler $successHandler,
        private RefreshTokenProviderInterface $tokenProvider,
        private string $parameterName
    ) {}

    public function authenticate(HttpFoundation\Request $request): PassportInterface
    {
        $credentials = (string) $request->request->get($this->parameterName);

        if (!str_contains($credentials, ':')) {

            throw new AuthenticationException('Invalid Token Format');
        }

        [$tokenId, $userProvidedVerification] = explode(':', $credentials);

        $token = $this->tokenProvider->getTokenWithIdentifier($tokenId);

        if (!$token instanceof RefreshTokenInterface) {
            throw new AuthenticationException('Token Does Not Exist');
        }

        if ($token->getValidUntil() <= time()) {
            throw new AuthenticationException('Token expired');
        }

        if (!$this->encoder->verify($userProvidedVerification, $token->getVerifier())) {
            throw new AuthenticationException('Token verification failed');
        }

        return new SelfValidatingPassport(new UserBadge($token->getUsername()));
    }

    public function supports(HttpFoundation\Request $request): bool
    {
        return null !== $request->request->get($this->parameterName);
    }

    public function onAuthenticationFailure(HttpFoundation\Request $request, AuthenticationException $exception): HttpFoundation\Response
    {
        return new HttpFoundation\JsonResponse(['error' => $exception->getMessage()], HttpFoundation\Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(HttpFoundation\Request $request, TokenInterface $token, string $firewallName): ?HttpFoundation\Response
    {
        return $this->successHandler->onAuthenticationSuccess($request, $token);
    }
}
