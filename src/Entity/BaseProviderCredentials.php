<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderCredentialsInterface;
use JMS\Serializer\Annotation\Exclude;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[ORM\MappedSuperclass]
abstract class BaseProviderCredentials implements ProviderCredentialsInterface
{

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'uuid')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected $id = null;

    #[ORM\ManyToOne(targetEntity: Tenant::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'tenant_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Exclude]
    private Tenant $tenant;

    #[ORM\Column(name: 'name', type: 'string', nullable: true)]
    protected ?string $name = null;

    #[ORM\Column(name: 'active', type: 'boolean', options: ['default' => 1])]
    protected bool $active = true;

    #[ORM\Column(name: "created_at", type: "datetime", nullable: false, options: ["default" => "CURRENT_TIMESTAMP"])]
    protected \DateTime $createdAt;

    public function getId()
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

}