<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Test\Unit;

use PHPUnit\Framework\TestCase;
use Yivoff\JwtRefreshBundle\Shared\TokenIdGenerator;

/**
 * @covers \Yivoff\JwtRefreshBundle\Shared\TokenIdGenerator
 *
 * @internal
 */
class TokenIdGeneratorTest extends TestCase
{
    public function testIdGeneration(): void
    {
        $generator = new TokenIdGenerator();
        $id1       = $generator->generateIdentifier(20);
        $id2       = $generator->generateIdentifier(20);
        $id3       = $generator->generateIdentifier(12);

        $v1 = $generator->generateVerifier(35);
        $v2 = $generator->generateVerifier(35);
        $v3 = $generator->generateVerifier(25);

        $this->assertEquals(20, strlen($id1));
        $this->assertEquals(20, strlen($id2));
        $this->assertEquals(12, strlen($id3));
        $this->assertNotEquals($id1, $id2);

        $this->assertEquals(35, strlen($v2));
        $this->assertEquals(35, strlen($v1));
        $this->assertEquals(25, strlen($v3));
        $this->assertNotEquals($v1, $v2);
    }
}
