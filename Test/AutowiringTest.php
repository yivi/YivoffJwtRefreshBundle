<?php declare(strict_types=1);

namespace Yivoff\Bundle\JwtRefresh\Test;

use Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\LexikJWTAuthenticationExtension;
use Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Yivoff\Bundle\JwtRefresh\DependencyInjection\YivoffJwtRefreshExtension;
use Yivoff\Bundle\JwtRefresh\YivoffJwtRefreshBundle;

class AutowiringTest extends TestCase
{

    public function testAutowiringWorksAsExpected(): void
    {
        $containerBuilder = $this->createContainerBuilder(
            [
                'framework'                => ['secret' => 'testing'],
                'lexik_jwt_authentication' => [
                    'secret_key'  => 'foo_bar',
                    'pass_phrase' => 'bar_foo',
                    'encoder'     => ['service' => 'app.fake_encoder'],
                ],
            ]
        );


    }

    private function createContainerBuilder(array $configs = []): ContainerBuilder
    {
        $container = new ContainerBuilder(
            new ParameterBag(
                [
                    'kernel.bundles'                                 =>
                        [
                            'FrameworkBundle'              => FrameworkBundle::class,
                            'LexikJWTAuthenticationBundle' => LexikJWTAuthenticationBundle::class,
                            'YivoffJwtRefreshBundle'       => YivoffJwtRefreshBundle::class,
                        ],
                    'kernel.bundles_metadata'                        => [],
                    'kernel.cache_dir'                               => __DIR__,
                    'kernel.debug'                                   => false,
                    'kernel.environment'                             => 'test',
                    'kernel.name'                                    => 'kernel',
                    'kernel.root_dir'                                => __DIR__,
                    'kernel.project_dir'                             => __DIR__,
                    'kernel.container_class'                         => 'AutowiringTestContainer',
                    'kernel.charset'                                 => 'utf8',
                    'env(base64:default::SYMFONY_DECRYPTION_SECRET)' => 'dummy',
                ]
            )
        );

        $container->registerExtension(new SecurityExtension());
        $container->registerExtension(new FrameworkExtension());
        $container->registerExtension(new LexikJWTAuthenticationExtension());
        $container->registerExtension(new YivoffJwtRefreshExtension());

        foreach ($configs as $extension => $config) {
            $container->loadFromExtension($extension, $config);
        }

        return $container;
    }

}
