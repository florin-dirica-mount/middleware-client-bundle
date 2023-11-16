<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection;

use Horeca\MiddlewareClientBundle\Repository\OrderNotificationRepository;
use Horeca\MiddlewareClientBundle\Repository\TenantRepository;
use Horeca\MiddlewareClientBundle\Repository\UserRepository;
use Horeca\MiddlewareClientBundle\Service\ProtocolActionsService;
use Horeca\MiddlewareClientBundle\Service\ProviderApiInterface;
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
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $this->loadServices($loader, $configs, $container);
    }

    public function getAlias(): string
    {
        return 'horeca_middleware_client';
    }

    private function defineRepositoryService(ContainerBuilder $container, $repositoryClass): void
    {
        $definition = new Definition($repositoryClass);
        $definition->setAutowired(true);
        $definition->setAutoconfigured(true);
        $definition->addTag('doctrine.repository_service');

        $container->setDefinition($repositoryClass, $definition);
    }

    private function loadServices(YamlFileLoader $loader, array $configs, ContainerBuilder $container): void
    {
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('horeca.order_notification_messenger_transport', $config['order_notification_messenger_transport']);
        $container->setParameter('horeca.provider_api_class', $config['provider_api_class']);
        $container->setParameter('horeca.provider_credentials_class', $config['provider_credentials_class']);

        // add provider service definition
        $providerDefinition = new Definition($config['provider_api_class']);
        $providerDefinition->setAutowired(true);
        $providerDefinition->setAutoconfigured(true);
        $container->setDefinition(ProviderApiInterface::class, $providerDefinition);

        // add provider service definition
        $providerDefinition = new Definition(ProtocolActionsService::class);
        $providerDefinition->setAutowired(true);
        $providerDefinition->setAutoconfigured(true);
        $container->setDefinition(ProtocolActionsService::class, $providerDefinition);

        // add repository services definition
        $this->defineRepositoryService($container, OrderNotificationRepository::class);
        $this->defineRepositoryService($container, UserRepository::class);
        $this->defineRepositoryService($container, TenantRepository::class);
    }

}
