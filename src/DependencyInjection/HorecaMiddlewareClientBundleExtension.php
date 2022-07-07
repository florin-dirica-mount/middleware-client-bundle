<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class HorecaMiddlewareClientBundleExtension extends ConfigurableExtension
{

    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $container->setParameter('horeca_middleware_client.horeca.base_url', $mergedConfig['horeca_middleware_client.horeca.base_url']);
        $container->setParameter('horeca_middleware_client.horeca.api_key', $mergedConfig['horeca_middleware_client.horeca.api_key']);
        $container->setParameter('horeca_middleware_client.horeca.shared_api_key', $mergedConfig['horeca_middleware_client.horeca.shared_api_key']);
        $container->setParameter('horeca_middleware_client.horeca.middleware_client_id', $mergedConfig['horeca_middleware_client.horeca.middleware_client_id']);
    }

}
