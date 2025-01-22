<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\DependencyInjection;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Yivoff\JwtRefreshBundle\Console\PurgeExpiredTokensCommand;
use Yivoff\JwtRefreshBundle\Contracts\HasherInterface;
use Yivoff\JwtRefreshBundle\Contracts\TokenIdGeneratorInterface;
use Yivoff\JwtRefreshBundle\EventListener\AttachRefreshToken;
use Yivoff\JwtRefreshBundle\Security\Authenticator;
use Yivoff\JwtRefreshBundle\Shared\Hasher;
use Yivoff\JwtRefreshBundle\Shared\TokenIdGenerator;
use Yivoff\JwtRefreshBundle\YivoffJwtRefreshBundle;

use function class_exists;

/**
 * @codeCoverageIgnore
 */
class YivoffJwtRefreshExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new BundleConfiguration();

        $config = $this->processConfiguration($configuration, $configs);

        /** @var string $value */
        foreach ($config as $key => $value) {
            $container->setParameter(YivoffJwtRefreshBundle::BUNDLE_PREFIX.'.'.$key, $value);
        }

        /** @phpstan-ignore-next-line  */
        $providerReference = new Reference($config['token_provider_service']);

        $container->register(AttachRefreshToken::class)
            ->setArgument(0, new Reference(HasherInterface::class))
            ->setArgument(1, new Reference(TokenIdGeneratorInterface::class))
            ->setArgument(2, $config['parameter_name'])
            ->setArgument(3, $config['token_ttl'])
            ->setArgument(4, $providerReference)
            ->addTag('kernel.event_listener', ['event' => 'lexik_jwt_authentication.on_authentication_success'])
        ;

        $container->setAlias(YivoffJwtRefreshBundle::BUNDLE_PREFIX.'.attach_refresh_token_listener', AttachRefreshToken::class)
            ->setPublic(true)
        ;

        $container->register(Authenticator::class)
            ->setArgument(0, new Reference(HasherInterface::class))
            ->setArgument(1, new Reference('lexik_jwt_authentication.handler.authentication_success'))
            ->setArgument(2, $providerReference)
            ->setArgument(3, new Reference(EventDispatcherInterface::class))
            ->setArgument(4, $config['parameter_name'])
        ;

        $container->setAlias(YivoffJwtRefreshBundle::BUNDLE_PREFIX.'.authenticator', Authenticator::class)
            ->setPublic(true)
        ;

        $container->register(HasherInterface::class)
            ->setArgument(0, '%kernel.secret%')
            ->setClass(Hasher::class)
        ;

        $container->setAlias(YivoffJwtRefreshBundle::BUNDLE_PREFIX.'.hasher', HasherInterface::class)
            ->setPublic(true)
        ;

        $container->register(TokenIdGeneratorInterface::class)
            ->setClass(TokenIdGenerator::class)
        ;

        $container->setAlias(YivoffJwtRefreshBundle::BUNDLE_PREFIX.'.token_id_generator', TokenIdGeneratorInterface::class);

        if (class_exists(Application::class)) {
            $container->register(PurgeExpiredTokensCommand::class)
                ->setArgument(0, $providerReference)
                ->addTag('console.command', ['command' => PurgeExpiredTokensCommand::getDefaultName()])
            ;
        }
    }

    public function getNamespace(): string
    {
        return 'https://yivoff.com/schema/dic/jwt_refresh_bundle';
    }

    public function getXsdValidationBasePath(): string
    {
        return __DIR__.'/../Resources/config/schema';
    }
}
