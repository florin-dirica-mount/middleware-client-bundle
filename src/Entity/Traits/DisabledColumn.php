<?php

namespace Horeca\MiddlewareClientBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * This field is meant to represent the object's id in the Tenant platform.
 */
trait DisabledColumn
{

    #[ORM\Column(name: 'disabled', type: 'boolean')]
    protected ?bool $disabled = null;

    public function getDisabled(): ?bool
    {
        return $this->disabled;
    }

    public function setDisabled(?bool $disabled): void
    {
        $this->disabled = $disabled;
    }

}
