<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection;

use Horeca\MiddlewareCommonLib\Service\HorecaApiInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

//        foreach ($config as $key => $value) {
//            $container->setParameter("horeca.$key", $value);
//        }
    }

    public function getAlias(): string
    {
        return 'horeca_middleware_client';
    }
}
