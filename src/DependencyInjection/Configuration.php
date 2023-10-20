<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('horeca_middleware_client');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()

                ->scalarNode('provider_api_class')
                    ->defaultValue('App\Service\ProviderApi')
                ->end()

                ->scalarNode('tenant_credentials_class')
                    ->defaultValue('App\Entity\TenantCredentials')
                ->end()

                ->scalarNode('order_notification_messenger_transport')
                    ->defaultValue('hmc_order_notification')
                ->end()

            ->end()
        ->end();

        return $treeBuilder;
    }
}
