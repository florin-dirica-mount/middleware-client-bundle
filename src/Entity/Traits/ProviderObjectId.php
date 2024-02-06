<?php

namespace Horeca\MiddlewareClientBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * This field is meant to represent the object's id in the Tenant platform.
 */
trait ProviderObjectId
{
    #[ORM\Column(name: 'provider_object_id', type: 'string', length: 36, nullable: true)]
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