<?php

namespace Horeca\MiddlewareClientBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\Enum\SerializationGroups;
use JMS\Serializer\Annotation as Serializer;

/**
 * This field is meant to represent the object's id in the Tenant platform.
 */
trait ProviderObjectLargeId
{
    #[ORM\Column(name: 'provider_object_id', type: 'string', length: 100, nullable: true)]
    #[Serializer\Groups([SerializationGroups::TenantOrderNotificationView])]
    protected ?string $providerObjectId = null;

    public function getProviderObjectId(): ?string
    {
        return $this->providerObjectId;
    }

    public function setProviderObjectId(?string $providerObjectId): void
    {
        $this->providerObjectId = $providerObjectId;
    }

}
