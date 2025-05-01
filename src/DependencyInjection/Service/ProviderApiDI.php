<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Service;

use Horeca\MiddlewareClientBundle\Service\ProviderApiInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait ProviderApiDI
{
    protected ProviderApiInterface $providerApi;

    #[Required]
    public function setProviderApi(ProviderApiInterface $providerApi): void
    {
        $this->providerApi = $providerApi;
    }
}
