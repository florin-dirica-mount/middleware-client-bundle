<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection;

use Horeca\MiddlewareClientBundle\Repository\OrderNotificationRepository;
use Horeca\MiddlewareClientBundle\Repository\TenantRepository;
use Horeca\MiddlewareClientBundle\Repository\UserRepository;
use Horeca\MiddlewareClientBundle\Service\OrderLogger;
use Horeca\MiddlewareClientBundle\Service\ProtocolActionsService;
use Horeca\MiddlewareClientBundle\Service\ProviderApiInterface;
use Horeca\MiddlewareClientBundle\Service\TenantApi;
use Horeca\MiddlewareClientBundle\Service\TenantApiInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class HorecaMiddlewareClientExtension extends Extension
{

    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../Resources/config')
        );

        $this->loadServices($loader, $configs, $container);
    }

    public function getAlias(): string
    {
        return 'horeca_middleware_client';
    }

    private function loadServices(YamlFileLoader $loader, array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('horeca.provider_api_class', $config['provider_api_class']);
        $container->setParameter('horeca.provider_credentials_class', $config['provider_credentials_class']);

        $loader->load('services.yaml');
    }

}
