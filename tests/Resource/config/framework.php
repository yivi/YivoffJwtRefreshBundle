<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {

    $configurator->extension('framework',
        [
            'secret' => 'secret',
            'test' => true,
            'router' =>
                [
                    'enabled' => true,
                    'resource' => 'kernel::loadRoutes',

                ]
        ]
    );
};
