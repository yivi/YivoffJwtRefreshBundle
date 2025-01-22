<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(Yivoff\JwtRefreshBundle\Test\Resource\InMemoryRefreshTokenProvider::class)
    ;

    $configurator->extension(
        'yivoff_jwt_refresh',
        [
            'token_provider_service' => Yivoff\JwtRefreshBundle\Test\Resource\InMemoryRefreshTokenProvider::class,
        ]
    );
};
