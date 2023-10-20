<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderCredentialsInterface;
use JMS\Serializer\Annotation\Exclude;

#[ORM\MappedSuperclass]
abstract class BaseTenantCredentials extends AbstractEntity implements ProviderCredentialsInterface
{

    #[ORM\OneToOne(targetEntity: Tenant::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'tenant_id', referencedColumnName: 'id', unique: true, nullable: false, onDelete: 'CASCADE')]
    #[Exclude]
    private Tenant $tenant;

    /**
     * Check if the credentials are valid. Throw an exception if not.
     */
    public abstract function isValid(): bool;

    /**
     * Return key-value pairs with the tenant credentials.
     */
    public abstract function getValues(): array;

    public function __toString()
    {
        return (string) $this->tenant;
    }

    public function getTenant(): Tenant
    {
        return $this->tenant;
    }

    public function setTenant(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

}