<?php declare(strict_types=1);

namespace Yivoff\Bundle\JwtRefresh\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('yivoff_jwt_refresh');

        $treeBuilder
            ->getRootNode()
                ->children()
                    ->integerNode('token_ttl')->defaultValue(3600)->end()
                    ->scalarNode('token_provider_service')->isRequired()->end()
                    ->scalarNode('parameter_name')->defaultValue('refresh_token')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

}
