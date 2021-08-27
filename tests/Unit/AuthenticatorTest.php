<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Test\Unit;

use DateTimeImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Yivoff\JwtRefreshBundle\Contracts\RefreshTokenProviderInterface;
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
        $handler  = $this->createMock(AuthenticationSuccessHandler::class);
        $provider = $this->createTokenProvider();

        $auth    = new Authenticator($this->createHasher(), $handler, $provider, 'test-param');
        $request = new Request();
        $request->request->set('test-param', str_repeat('a', 20).':'.str_repeat('b', 20));

        $passport = $auth->authenticate($request);

        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);

        $badge = $passport->getBadge(UserBadge::class);

        $this->assertInstanceOf(UserBadge::class, $badge);
        $this->assertEquals('yivoff', $badge->getUserIdentifier());
    }

    public function testInvalidToken(): void
    {
        $handler  = $this->createMock(AuthenticationSuccessHandler::class);
        $provider = $this->createTokenProvider();

        $auth    = new Authenticator($this->createHasher(), $handler, $provider, 'test-param');
        $request = new Request();
        $request->request->set('test-param', str_repeat('a', 20).'-'.str_repeat('b', 20));

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid Token Format');
        $passport = $auth->authenticate($request);
    }

    public function testMissingToken(): void
    {
        $handler  = $this->createMock(AuthenticationSuccessHandler::class);
        $provider = $this->createTokenProvider();

        $auth    = new Authenticator($this->createHasher(), $handler, $provider, 'test-param');
        $request = new Request();
        $request->request->set('test-param', str_repeat('c', 20).':'.str_repeat('b', 20));

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Token Does Not Exist');
        $passport = $auth->authenticate($request);
    }

    public function testExpiredToken(): void
    {
        $handler  = $this->createMock(AuthenticationSuccessHandler::class);
        $provider = $this->createTokenProvider();

        $auth    = new Authenticator($this->createHasher(), $handler, $provider, 'test-param');
        $request = new Request();
        $request->request->set('test-param', str_repeat('x', 20).':'.str_repeat('b', 20));

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Token expired');
        $passport = $auth->authenticate($request);
    }

    public function testHashFail(): void
    {
        $handler  = $this->createMock(AuthenticationSuccessHandler::class);
        $provider = $this->createTokenProvider();

        $auth    = new Authenticator($this->createHasher(false), $handler, $provider, 'test-param');
        $request = new Request();
        $request->request->set('test-param', str_repeat('a', 20).':'.str_repeat('b', 20));

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Token verification failed');
        $passport = $auth->authenticate($request);
    }

    public function testSupports(): void
    {
        $handler  = $this->createMock(AuthenticationSuccessHandler::class);
        $provider = $this->createTokenProvider();

        $auth    = new Authenticator($this->createHasher(false), $handler, $provider, 'test-param');
        $request = new Request();
        $request->request->set('test-param', str_repeat('a', 20).':'.str_repeat('b', 20));

        $this->assertTrue($auth->supports($request));
    }

    public function testAuthFailure(): void
    {
        $handler  = $this->createMock(AuthenticationSuccessHandler::class);
        $provider = $this->createTokenProvider();

        $auth      = new Authenticator($this->createHasher(false), $handler, $provider, 'test-param');
        $request   = new Request();
        $exception = new AuthenticationException('Bad Auth');

        $response = $auth->onAuthenticationFailure($request, $exception);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertJson('{"error": "Bad Auth"}', $response->getContent());
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
