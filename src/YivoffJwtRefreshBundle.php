<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Yivoff\JwtRefreshBundle\DependencyInjection\YivoffJwtRefreshExtension;

use function dirname;

class YivoffJwtRefreshBundle implements BundleInterface
{
    public const BUNDLE_PREFIX = 'yivoff_jwt_refresh';

    protected null|ExtensionInterface|false $extension = null;
    protected ?ContainerInterface $container           = null;

    public function boot(): void {}

    public function shutdown(): void {}

    public function build(ContainerBuilder $container): void {}

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new YivoffJwtRefreshExtension();
    }

    public function getName(): string
    {
        return 'YivoffJwtRefreshBundle';
    }

    public function getNamespace(): string
    {
        return 'Yivoff\\JwtRefreshBundle';
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }

    public function setContainer(?ContainerInterface $container): void
    {
        $this->container = $container;
    }
}
