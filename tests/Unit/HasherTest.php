<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Test\Unit;

use PHPUnit\Framework\TestCase;
use Yivoff\JwtRefreshBundle\Shared\Hasher;

/**
 * @covers \Yivoff\JwtRefreshBundle\Shared\Hasher
 *
 * @internal
 */
class HasherTest extends TestCase
{
    public function testHashChecksOut(): void
    {
        $secret = 'very-secret';
        $hasher = new Hasher('very-secret');

        $hash = $hasher->hash($secret);

        $this->assertTrue($hasher->verify($secret, $hash));
    }
}
