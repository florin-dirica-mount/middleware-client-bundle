<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection;

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

        $providerDefinition = new Definition($config['provider_api_class']);
        $providerDefinition->setAutowired(true);
        $container->setDefinition(ProviderApiInterface::class, $providerDefinition);
    }

    public function getAlias(): string
    {
        return 'horeca_middleware_client';
    }
}
