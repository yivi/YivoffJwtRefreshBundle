<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Test\DependencyInjection;

use Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle;
use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Yivoff\JwtRefreshBundle\Contracts\HasherInterface;
use Yivoff\JwtRefreshBundle\Contracts\TokenIdGeneratorInterface;
use Yivoff\JwtRefreshBundle\EventListener\AttachRefreshToken;
use Yivoff\JwtRefreshBundle\Security\Authenticator;
use Yivoff\JwtRefreshBundle\YivoffJwtRefreshBundle;

/**
 * @internal
 * @coversNothing
 */
class BundleInitializationTest extends KernelTestCase
{
    public function testServiceAndAliasesCreation(): void
    {
        self::bootKernel([
            'config' => static function (TestKernel $kernel): void {
                $kernel->addTestConfig(__DIR__.'/../Resource/config/config.php');
            },
        ]);
        $container = self::getContainer();

        // tests if your services exists
        $this->assertTrue($container->has(AttachRefreshToken::class));
        $this->assertTrue($container->has(YivoffJwtRefreshBundle::BUNDLE_PREFIX.'.attach_refresh_token_listener'));

        $this->assertTrue($container->has(Authenticator::class));
        $this->assertTrue($container->has(YivoffJwtRefreshBundle::BUNDLE_PREFIX.'.authenticator'));

        $this->assertTrue($container->has(HasherInterface::class));
        $this->assertTrue($container->has(YivoffJwtRefreshBundle::BUNDLE_PREFIX.'.hasher'));

        $this->assertTrue($container->has(TokenIdGeneratorInterface::class));
        $this->assertTrue($container->has(YivoffJwtRefreshBundle::BUNDLE_PREFIX.'.token_id_generator'));
    }

    protected static function createKernel(array $options = []): TestKernel
    {
        /**
         * @var TestKernel $kernel
         */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(SecurityBundle::class);
        $kernel->addTestBundle(LexikJWTAuthenticationBundle::class);
        $kernel->addTestBundle(YivoffJwtRefreshBundle::class);

        $kernel->handleOptions($options);

        return $kernel;
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }
}
