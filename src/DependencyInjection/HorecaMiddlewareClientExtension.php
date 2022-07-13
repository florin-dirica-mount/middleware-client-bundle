<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection;

use Horeca\MiddlewareClientBundle\Repository\Log\RequestLogRepository;
use Horeca\MiddlewareClientBundle\Repository\OrderNotificationRepository;
use Horeca\MiddlewareClientBundle\Repository\UserRepository;
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
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('horeca.api_key', $config['api_key']);
        $container->setParameter('horeca.enable_request_exception_logging', $config['enable_request_exception_logging']);
        $container->setParameter('horeca.order_notification_messenger_transport', $config['order_notification_messenger_transport']);
        $container->setParameter('horeca.provider_api_class', $config['provider_api_class']);

        // add provider service definition
        $providerDefinition = new Definition($config['provider_api_class']);
        $providerDefinition->setAutowired(true);
        $providerDefinition->setAutoconfigured(true);
        $container->setDefinition(ProviderApiInterface::class, $providerDefinition);

        // add repository services definition
        $this->defineRepositoryService($container, RequestLogRepository::class);
        $this->defineRepositoryService($container, OrderNotificationRepository::class);
        $this->defineRepositoryService($container, UserRepository::class);
    }

    public function getAlias(): string
    {
        return 'horeca_middleware_client';
    }

    private function defineRepositoryService(ContainerBuilder $container, $repositoryClass)
    {
        $definition = new Definition($repositoryClass);
        $definition->setAutowired(true);
        $definition->setAutoconfigured(true);
        $definition->addTag('doctrine.repository_service');

        $container->setDefinition($repositoryClass, $definition);
    }
}
