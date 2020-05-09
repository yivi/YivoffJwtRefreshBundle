<?php declare(strict_types=1);

namespace Yivoff\Bundle\JwtRefresh\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Yivoff\Bundle\JwtRefresh\Contracts\EncoderInterface;
use Yivoff\Bundle\JwtRefresh\Contracts\RefreshTokenProviderInterface;

final class Authenticator extends AbstractGuardAuthenticator
{

    private EncoderInterface $encoder;
    private AuthenticationSuccessHandler $successHandler;
    private RefreshTokenProviderInterface $tokenProvider;
    private string $parameterName;


    public function __construct(
        EncoderInterface $encoder,
        AuthenticationSuccessHandler $successHandler,
        RefreshTokenProviderInterface $tokenProvider,
        string $parameterName
    ) {
        $this->tokenProvider  = $tokenProvider;
        $this->parameterName  = $parameterName;
        $this->encoder        = $encoder;
        $this->successHandler = $successHandler;
    }


    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new JsonResponse(
            [
                'message' => 'Authentication Required. Invalid or missing token.',
            ],
            Response::HTTP_UNAUTHORIZED
        );
    }

    public function supports(Request $request): bool
    {
        return $request->request->get($this->parameterName) !== null;
    }

    public function getCredentials(Request $request)
    {
        return $request->request->get($this->parameterName);
    }

    public function getUser($credentials, UserProviderInterface $userProvider): UserInterface
    {
        if (strpos((string)$credentials, ':') === false) {
            throw new AuthenticationException('Invalid Token Format');
        }

        [$identifier, $verifier] = explode(':', (string)$credentials);

        $token = $this->tokenProvider->getTokenWithIdentifier($identifier);

        if (null === $token) {
            throw new AuthenticationException('Token Does Not Exist');
        }

        if ($token->getValidUntil() <= time()) {
            throw new AuthenticationException('Token has been invalidated');
        }

        if ( ! $this->encoder->verify($verifier, $token->getVerifier())) {
            throw new AuthenticationException('Invalid token sent');
        }

        return $userProvider->loadUserByUsername($token->getUsername());
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): ?Response
    {
        return $this->successHandler->onAuthenticationSuccess($request, $token);
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }

}
