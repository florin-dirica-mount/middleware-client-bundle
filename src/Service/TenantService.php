<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\TenantRepositoryDI;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareClientBundle\Exception\MiddlewareClientException;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderCredentialsInterface;
use JMS\Serializer\SerializerInterface;

class TenantService
{
    use TenantRepositoryDI;

    public function __construct(protected string               $providerCredentialsClass,
                                protected SerializerInterface  $serializer,
                                protected ProviderApiInterface $providerApi)
    {
    }

    /**
     * @return ProviderCredentialsInterface
     * @throws MiddlewareClientException
     */
    public function compileTenantCredentials(Tenant $tenant, ?array $inputCredentials = null)
    {
        if (empty($inputCredentials)) {
            $credentials = $this->tenantRepository->findTenantCredentials($tenant, $this->providerCredentialsClass);
        } else {
            $credentialsJson = $this->serializer->serialize($inputCredentials, 'json');
            $credentials = $this->serializer->deserialize($credentialsJson, $this->providerCredentialsClass, 'json');
        }

        if (empty($credentials)) {
            throw new MiddlewareClientException('Tenant credentials not found or invalid.');
        }

        return $credentials;
    }
}