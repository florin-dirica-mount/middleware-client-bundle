<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\TenantRepositoryDI;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareClientBundle\Exception\ApiException;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderCredentialsInterface;
use JMS\Serializer\SerializerInterface;

/**
 * @template T of ProviderCredentialsInterface
 * @template-extends ProviderCredentialsInterface<T>
 */
class TenantService
{
    use TenantRepositoryDI;

    /**
     * @param class-string<T> $providerCredentialsClass
     */
    public function __construct(protected string               $providerCredentialsClass,
                                protected SerializerInterface  $serializer,
                                protected ProviderApiInterface $providerApi)
    {
    }

    /**
     * @return ProviderCredentialsInterface<T>
     * @throws ApiException
     */
    public function compileTenantCredentials(Tenant $tenant, ?array $inputCredentials = null): ProviderCredentialsInterface
    {
        if (empty($inputCredentials)) {
            $credentials = $this->tenantRepository->findTenantCredentials($tenant, $this->providerCredentialsClass);
        } else {
            $credentialsJson = $this->serializer->serialize($inputCredentials, 'json');
            $credentials = $this->serializer->deserialize($credentialsJson, $this->providerApi->getProviderCredentialsClass(), 'json');
        }

        if (empty($credentials)) {
            throw new ApiException('Tenant credentials not found or invalid.');
        }

        return $credentials;
    }
}