<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Yivoff\JwtTokenRefresh\Contracts\EncoderInterface;
use Yivoff\JwtTokenRefresh\Contracts\IdGeneratorInterface;
use Yivoff\JwtTokenRefresh\EventListener\AttachRefreshToken;
use Yivoff\JwtTokenRefresh\EventListener\AuthenticationSuccessful;
use Yivoff\JwtTokenRefresh\Security\Authenticator;
use Yivoff\JwtTokenRefresh\Service\Encoder;
use Yivoff\JwtTokenRefresh\Service\IdGenerator;

return static function (ContainerConfigurator $container) {
    $services = $container->services()
                          ->defaults()
                          ->private();

    $services
        ->set(AttachRefreshToken::class)
        ->tag('kernel.event_listener', ['event' => 'lexik_jwt_authentication.on_authentication_success'])
        ->args(
            [
                ref(EncoderInterface::class),
                ref(IdGeneratorInterface::class),
                null,
                null,
                null,
            ]
        )
        ->alias('yivoff.token_refresh.attach_token_listener', AttachRefreshToken::class);

    $services
        ->set(EncoderInterface::class)
        ->arg(0, null)
        ->class(Encoder::class)
        ->alias('yivoff.token_refresh.encoder', EncoderInterface::class);

    $services
        ->set(IdGeneratorInterface::class)
        ->class(IdGenerator::class);

    $services
        ->set(Authenticator::class)
        ->args(
            [
                ref('yivoff.token_refresh.encoder'),
                ref('lexik_jwt_authentication.handler.authentication_success'),
                null,
                null,
            ]
        )
        ->alias('yivoff.token_refresh.authenticator', Authenticator::class);
};
