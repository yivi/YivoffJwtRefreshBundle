<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Test\Functional;

use DateTimeImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle;
use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Yivoff\JwtRefreshBundle\Console\PurgeExpiredTokensCommand;
use Yivoff\JwtRefreshBundle\Contracts\RefreshTokenInterface;
use Yivoff\JwtRefreshBundle\Model\RefreshToken;
use Yivoff\JwtRefreshBundle\YivoffJwtRefreshBundle;

/**
 * @covers \Yivoff\JwtRefreshBundle\Console\PurgeExpiredTokensCommand
 *
 * @internal
 */
class PurgeCommandTest extends KernelTestCase
{
    public function testNonPurgableProviderFails(): void
    {
        $kernel = self::bootKernel([
            'config' => static function (TestKernel $kernel): void {
                $kernel->addTestConfig(__DIR__.'/../Resource/config/config.php');
                $kernel->addTestConfig(__DIR__.'/../Resource/config/security-config.yaml');
            },
        ]);

        $container = self::getContainer();

        /** @var \Yivoff\JwtRefreshBundle\Test\Resource\InMemoryRefreshTokenProvider $provider */
        $provider = $container->get($container->getParameter('yivoff_jwt_refresh.token_provider_service'));

        $provider->add(new RefreshToken('foo', '11111', 'baz', new DateTimeImmutable('-5 seconds')));
        $provider->add(new RefreshToken('bar', '22222', 'baz', new DateTimeImmutable('-5 seconds')));
        $provider->add(new RefreshToken('baz', '33333', 'baz', new DateTimeImmutable('+5 seconds')));

        $application   = new Application($kernel);
        $command       = $application->find(PurgeExpiredTokensCommand::getDefaultName());
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());

        $this->assertInstanceOf(RefreshTokenInterface::class, $provider->getTokenWithIdentifier('11111'));
        $this->assertInstanceOf(RefreshTokenInterface::class, $provider->getTokenWithIdentifier('22222'));
        $this->assertInstanceOf(RefreshTokenInterface::class, $provider->getTokenWithIdentifier('33333'));
    }

    public function testPurgableProviderPurges(): void
    {
        $kernel = self::bootKernel([
            'config' => static function (TestKernel $kernel): void {
                $kernel->addTestConfig(__DIR__.'/../Resource/config/config_purgable.php');
                $kernel->addTestConfig(__DIR__.'/../Resource/config/security-config.yaml');
            },
        ]);

        $container = self::getContainer();

        /** @var \Yivoff\JwtRefreshBundle\Test\Resource\InMemoryRefreshTokenProvider $provider */
        $provider = $container->get($container->getParameter('yivoff_jwt_refresh.token_provider_service'));

        $provider->add(new RefreshToken('foo', '11111', 'baz', new DateTimeImmutable('-5 seconds')));
        $provider->add(new RefreshToken('bar', '22222', 'baz', new DateTimeImmutable('-5 seconds')));
        $provider->add(new RefreshToken('baz', '33333', 'baz', new DateTimeImmutable('+15 seconds')));

        $application   = new Application($kernel);
        $command       = $application->find(PurgeExpiredTokensCommand::getDefaultName());
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertNull($provider->getTokenWithIdentifier('11111'));
        $this->assertNull($provider->getTokenWithIdentifier('22222'));
        $this->assertInstanceOf(RefreshTokenInterface::class, $provider->getTokenWithIdentifier('33333'));
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
