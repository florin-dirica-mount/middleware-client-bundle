<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('horeca_middleware_client');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('provider_api_class')
                    ->defaultValue('App\Service\ProviderApi')
                ->end()
                ->booleanNode('enable_request_exception_logging')
                    ->defaultTrue()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
