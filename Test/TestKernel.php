<?php declare(strict_types=1);

namespace Yivoff\Bundle\JwtRefresh\Test;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Yivoff\Bundle\JwtRefresh\YivoffJwtRefreshBundle;

class TestKernel extends Kernel
{

    private string $extension;

    public function __construct(string $env, bool $debug, string $extension = 'yaml')
    {
        $this->extension = $extension;
        parent::__construct($env, $debug);
    }

    public function registerBundles(): iterable
    {
        return [new YivoffJwtRefreshBundle()];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/resources/config.' . $this->extension);
    }

}
