<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Test\Resource;

use Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Yivoff\JwtRefreshBundle\YivoffJwtRefreshBundle;

class AppKernel extends BaseKernel
{

    static array $testBundles = [
        FrameworkBundle::class,
        LexikJWTAuthenticationBundle::class,
        YivoffJwtRefreshBundle::class,
        SecurityBundle::class,
    ];

    static array $testConfigs = [
        __DIR__ . '/config/framework.php' => 'php',
        __DIR__ . '/config/lexik.php' => 'php',
        __DIR__ . '/config/bundle-non-purgable.php' => null,
        __DIR__ . '/config/security.yaml' => 'yaml'
    ];

    /**
     * @return \Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     */
    public function registerBundles(): iterable
    {
        foreach (self::$testBundles as $bundle) {
            yield new $bundle();
        }
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        foreach (self::$testConfigs as $config => $format) {
            $loader->load($config, $format);
        }
    }

    public function getCacheDir(): string
    {
        return 'var/cache';
    }

    public function getLogDir(): string
    {
        return 'var/logs';
    }

    public function getProjectDir(): string
    {
        return __DIR__.'/';
    }
}
