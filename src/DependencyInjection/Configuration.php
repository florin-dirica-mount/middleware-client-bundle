<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('horeca_middleware_client');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('horeca_middleware_client')
                    ->children()
                            ->arrayNode('horeca')
                                ->children()
                                    ->variableNode('base_url')
                                        ->defaultValue('%env(resolve:HORECA_BASE_URL)%')
                                    ->end()
                                    ->variableNode('webhook_api_key')
                                        ->defaultValue('%env(resolve:HORECA_API_KEY)%')
                                    ->end()
                                    ->variableNode('shared_api_key')
                                        ->defaultValue('%env(resolve:HORECA_SHARED_KEY)%')
                                    ->end()
                                    ->variableNode('middleware_client_id')
                                        ->defaultValue('%env(resolve:MIDDLEWARE_CLIENT_ID)%')
                                    ->end()
                                ->end()
                            ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

}
