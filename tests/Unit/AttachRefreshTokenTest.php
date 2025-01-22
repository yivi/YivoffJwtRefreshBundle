<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Test\Unit;

use DateTimeImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Yivoff\JwtRefreshBundle\Contracts\RefreshTokenInterface;
use Yivoff\JwtRefreshBundle\EventListener\AttachRefreshToken;
use Yivoff\JwtRefreshBundle\Test\Resource\InMemoryRefreshTokenProvider;

use function explode;

/**
 *
 * @internal
 */
#[CoversClass(AttachRefreshToken::class)]
class AttachRefreshTokenTest extends TestCase
{
    use MockerTrait;

    public function testEventListener(): void
    {
        $tokenProvider = new InMemoryRefreshTokenProvider();

        $expiryDate = new DateTimeImmutable('+10 seconds');
        $listener   = new AttachRefreshToken($this->createHasher(), $this->createDummyIdGenerator(), 'auth_param', 10, $tokenProvider);

        $event = new AuthenticationSuccessEvent([], new InMemoryUser('yivoff', null), new Response());
        $listener->__invoke($event);
        $data = $event->getData();

        $this->assertNotEmpty($data['auth_param']);
        $this->assertMatchesRegularExpression('|[0-9a-z]{20}:[0-9a-z]{32}|', $data['auth_param']);
        [$tokenId, $verifier] = explode(':', $data['auth_param']);

        $token = $tokenProvider->getTokenForUsername('yivoff');
        $this->assertInstanceOf(RefreshTokenInterface::class, $token);

        $this->assertEquals($tokenId, $token->getIdentifier());
        $this->assertEquals($expiryDate->getTimestamp(), $token->getValidUntil());
    }
}
