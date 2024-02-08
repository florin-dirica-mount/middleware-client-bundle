<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name: 'tenant_webhooks')]
#[UniqueEntity(fields: ['name', 'tenant'], message: 'A webhook with this name already exists for this tenant')]
class TenantWebhook extends DefaultEntity
{
    #[ORM\ManyToOne(targetEntity: Tenant::class, cascade: ["persist"], inversedBy: 'webhooks')]
    #[ORM\JoinColumn(name: "tenant_id", referencedColumnName: "id", nullable: true, onDelete: "CASCADE")]
    #[Serializer\Exclude]
    protected ?Tenant $tenant = null;

    #[ORM\Column(name: 'name', type: 'string', nullable: false)]
    protected string $name;

    #[ORM\Column(name: 'path', type: 'string', nullable: false)]
    protected string $path;

    #[ORM\Column(name: 'enabled', type: 'boolean', options: ['default' => 1])]
    protected bool $enabled = true;

    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function setTenant(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

}