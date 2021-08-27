<?php declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Test\Unit;

use PHPUnit\Framework\TestCase;
use Yivoff\JwtRefreshBundle\Shared\Hasher;

/**
 * @covers \Yivoff\JwtRefreshBundle\Shared\Hasher
 */
class HasherTest extends TestCase
{

    public function testHashChecksOut()
    {
        $secret = 'very-secret';
        $hasher = new Hasher('very-secret');

        $hash = $hasher->hash($secret);

        $this->assertTrue($hasher->verify($secret, $hash));
    }

}
