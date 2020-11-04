<?php declare(strict_types=1);

namespace Yivoff\Bundle\JwtRefresh\Test;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BasicTest extends KernelTestCase
{

    public function testServiceAreCorrectlyRegistered(): void
    {
        self::bootKernel();
        $service = self::$container->get('yivoff.token_refresh.authenticator');

        $this->assertNotNull($service);
        $this->assertSame(\Yivoff\Bundle\JwtRefresh\Security\Authenticator::class, get_class($service));
    }

    protected function setUp(): void
    {
        static::bootKernel();
    }
}
