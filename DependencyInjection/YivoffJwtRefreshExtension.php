<?php declare(strict_types=1);

namespace Yivoff\JwtTokenRefresh\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Yivoff\JwtTokenRefresh\Contracts\EncoderInterface;
use Yivoff\JwtTokenRefresh\EventListener\AttachRefreshToken;
use Yivoff\JwtTokenRefresh\Security\Authenticator;

class YivoffJwtRefreshExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);
        $loader        = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.php');

        $providerReference = new Reference($config['token_provider_service']);

        $container->getDefinition(AttachRefreshToken::class)
                  ->setArgument(2, $config['parameter_name'])
                  ->setArgument(3, $config['token_ttl'])
                  ->setArgument(4, $providerReference);

        $container->getDefinition(Authenticator::class)
                  ->setArgument(2, $providerReference)
                  ->setArgument(3, $config['parameter_name']);

        $container->getDefinition(EncoderInterface::class)
                  ->setArgument(0, '%kernel.secret%');
    }
}
