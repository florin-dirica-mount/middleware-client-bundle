<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Any entity that contains data owned by a Tenant should extend this class.
 */
abstract class TenantAwareEntity extends DefaultEntity
{
    #[ORM\ManyToOne(targetEntity: Tenant::class, cascade: ["persist"])]
    #[ORM\JoinColumn(name: "tenant_id", referencedColumnName: "id", nullable: true, onDelete: "CASCADE")]
    #[Serializer\Exclude]
    protected ?Tenant $tenant = null;

    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function setTenant(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }
}