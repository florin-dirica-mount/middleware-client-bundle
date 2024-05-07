<?php

namespace Horeca\MiddlewareClientBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\Enum\SerializationGroups;
use JMS\Serializer\Annotation as Serializer;

/**
 * This field is meant to represent the object's id in the Tenant platform.
 */
trait TenantObjectId
{
    #[ORM\Column(name: 'tenant_object_id', type: 'string', length: 36, nullable: true)]
    #[Serializer\Groups([SerializationGroups::TenantOrderNotificationView])]
    protected ?string $tenantObjectId;

    public function getTenantObjectId(): ?string
    {
        return $this->tenantObjectId;
    }

    public function setTenantObjectId(?string $tenantObjectId): void
    {
        $this->tenantObjectId = $tenantObjectId;
    }
}
