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

                ->scalarNode('api_key')->end()

                ->scalarNode('provider_api_class')
                    ->defaultValue('App\Service\ProviderApi')
                ->end()

                ->booleanNode('enable_request_exception_logging')
                    ->defaultTrue()
                ->end()

                ->scalarNode('order_notification_messenger_transport')
                    ->defaultValue('hmc_order_notification')
                ->end()

            ->end()
        ->end();

        return $treeBuilder;
    }
}
