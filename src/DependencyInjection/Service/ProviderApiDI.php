<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Service;

use Horeca\MiddlewareClientBundle\Service\ProviderApiInterface;

trait ProviderApiDI
{
    protected ProviderApiInterface $providerApi;

    /**
     * @required
     */
    public function setProviderApi(ProviderApiInterface $providerApi): void
    {
        $this->providerApi = $providerApi;
    }
}
