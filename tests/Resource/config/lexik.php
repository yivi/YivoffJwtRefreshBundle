<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {

    // a default configuration for Lexik JWT
    $configurator->extension(
        'lexik_jwt_authentication',
        [
            'secret_key'       => __DIR__.'/../jwt_keys/private.pem',
            'public_key'       => __DIR__.'/../jwt_keys/public.pem',
            'pass_phrase'      => 'secret',
            'token_ttl'        => '3600',
            'token_extractors' => [
                'authorization_header' => ['enabled' => true, 'prefix' => 'Bearer', 'name' => 'Authorization'],
                'cookie'               => ['enabled' => false, 'name' => 'BEARER'],
                'query_parameter'      => ['enabled' => false, 'name' => 'bearer'],
            ],
        ]
    );
};
