<?php

namespace Horeca\MiddlewareClientBundle;

use Horeca\MiddlewareClientBundle\DependencyInjection\HorecaMiddlewareClientExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HorecaMiddlewareClientBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new HorecaMiddlewareClientExtension();
        }

        return $this->extension;
    }
}
