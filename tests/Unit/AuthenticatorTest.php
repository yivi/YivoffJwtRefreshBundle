<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Test\Unit;

use DateTimeImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Yivoff\JwtRefreshBundle\Contracts\RefreshTokenProviderInterface;
use Yivoff\JwtRefreshBundle\Event\JwtRefreshTokenFailed;
use Yivoff\JwtRefreshBundle\Exception\FailType;
use Yivoff\JwtRefreshBundle\Exception\PayloadInvalidException;
use Yivoff\JwtRefreshBundle\Exception\TokenExpiredException;
use Yivoff\JwtRefreshBundle\Exception\TokenInvalidException;
use Yivoff\JwtRefreshBundle\Exception\TokenNotFoundException;
use Yivoff\JwtRefreshBundle\Model\RefreshToken;
use Yivoff\JwtRefreshBundle\Security\Authenticator;
use Yivoff\JwtRefreshBundle\Test\Resource\InMemoryRefreshTokenProvider;

/**
 * @covers \Yivoff\JwtRefreshBundle\Security\Authenticator
 *
 * @internal
 */
class AuthenticatorTest extends TestCase
{
    use MockerTrait;

    public function testSuccessful(): void
    {
        $handler         = $this->createMock(AuthenticationSuccessHandler::class);
        $eventDispatcher = new EventDispatcherSpy();
        $provider        = $this->createTokenProvider();

        $auth    = new Authenticator($this->createHasher(), $handler, $provider, $eventDispatcher, 'test-param');
        $request = new Request();
        $request->request->set('test-param', str_repeat('a', 20).':'.str_repeat('b', 20));

        $passport = $auth->authenticate($request);

        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);

        $badge = $passport->getBadge(UserBadge::class);

        $this->assertEquals(true, $passport->getAttribute('yivoff_refresh_auth', false));
        $this->assertInstanceOf(UserBadge::class, $badge);
        $this->assertEquals('yivoff', $badge->getUserIdentifier());
    }

    public function testInvalidToken(): void
    {
        $handler            = $this->createMock(AuthenticationSuccessHandler::class);
        $eventDispatcherSpy = new EventDispatcherSpy();
        $provider           = $this->createTokenProvider();

        $auth    = new Authenticator($this->createHasher(), $handler, $provider, $eventDispatcherSpy, 'test-param');
        $request = new Request();
        $request->request->set('test-param', str_repeat('a', 20).'-'.str_repeat('b', 20));

        $this->expectException(PayloadInvalidException::class);
        $auth->authenticate($request);

        $event = $eventDispatcherSpy->getEventByName(JwtRefreshTokenFailed::class);
        $this->assertInstanceOf(JwtRefreshTokenFailed::class, $event);
        $this->assertEquals(FailType::INVALID, $event->failType);
    }

    public function testTokenNotFound(): void
    {
        $handler            = $this->createMock(AuthenticationSuccessHandler::class);
        $eventDispatcherSpy = new EventDispatcherSpy();
        $provider           = $this->createTokenProvider();

        $auth    = new Authenticator($this->createHasher(), $handler, $provider, $eventDispatcherSpy, 'test-param');
        $request = new Request();
        $request->request->set('test-param', str_repeat('c', 20).':'.str_repeat('b', 20));

        $this->expectException(TokenNotFoundException::class);
        $auth->authenticate($request);

        $event = $eventDispatcherSpy->getEventByName(JwtRefreshTokenFailed::class);
        $this->assertInstanceOf(JwtRefreshTokenFailed::class, $event);
        $this->assertEquals(FailType::NOT_FOUND, $event->failType);
    }

    public function testExpiredToken(): void
    {
        $handler            = $this->createMock(AuthenticationSuccessHandler::class);
        $eventDispatcherSpy = new EventDispatcherSpy();
        $provider           = $this->createTokenProvider();

        $auth    = new Authenticator($this->createHasher(), $handler, $provider, $eventDispatcherSpy, 'test-param');
        $request = new Request();
        $request->request->set('test-param', str_repeat('x', 20).':'.str_repeat('b', 20));

        $this->expectException(TokenExpiredException::class);
        $auth->authenticate($request);

        $event = $eventDispatcherSpy->getEventByName(JwtRefreshTokenFailed::class);
        $this->assertInstanceOf(JwtRefreshTokenFailed::class, $event);
        $this->assertEquals(FailType::EXPIRED, $event->failType);
    }

    public function testHashFail(): void
    {
        $handler            = $this->createMock(AuthenticationSuccessHandler::class);
        $eventDispatcherSpy = new EventDispatcherSpy();
        $provider           = $this->createTokenProvider();

        $auth    = new Authenticator($this->createHasher(false), $handler, $provider, $eventDispatcherSpy, 'test-param');
        $request = new Request();
        $request->request->set('test-param', str_repeat('a', 20).':'.str_repeat('b', 20));

        $this->expectException(TokenInvalidException::class);
        $auth->authenticate($request);

        $event = $eventDispatcherSpy->getEventByName(JwtRefreshTokenFailed::class);
        $this->assertInstanceOf(JwtRefreshTokenFailed::class, $event);
        $this->assertEquals(FailType::INVALID, $event->failType);
    }

    public function testSupports(): void
    {
        $handler         = $this->createMock(AuthenticationSuccessHandler::class);
        $eventDispatcher = new EventDispatcherSpy();
        $provider        = $this->createTokenProvider();

        $auth    = new Authenticator($this->createHasher(false), $handler, $provider, $eventDispatcher, 'test-param');
        $request = new Request();
        $request->request->set('test-param', str_repeat('a', 20).':'.str_repeat('b', 20));

        $this->assertTrue($auth->supports($request));
    }

    public function testAuthFailure(): void
    {
        $handler         = $this->createMock(AuthenticationSuccessHandler::class);
        $eventDispatcher = new EventDispatcherSpy();
        $provider        = $this->createTokenProvider();

        $auth      = new Authenticator($this->createHasher(false), $handler, $provider, $eventDispatcher, 'test-param');
        $request   = new Request();
        $exception = new TokenInvalidException('abc', 'def');

        $response = $auth->onAuthenticationFailure($request, $exception);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertJson('{"error": "Invalid Token. Token Id: abc. User Id: def"}', $response->getContent());
    }

    private function createTokenProvider(): RefreshTokenProviderInterface
    {
        $provider = new InMemoryRefreshTokenProvider();
        $provider->add(
            new RefreshToken(
                'yivoff',
                str_repeat('a', 20),
                str_repeat('b', 32),
                new DateTimeImmutable('+30 seconds')
            )
        );

        $provider->add(
            new RefreshToken(
                'm.twain',
                str_repeat('x', 20),
                str_repeat('b', 32),
                new DateTimeImmutable('-30 seconds')
            )
        );

        return $provider;
    }
}
