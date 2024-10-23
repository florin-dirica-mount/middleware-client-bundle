<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\Repository\TenantRepository;

#[ORM\Entity(repositoryClass: TenantRepository::class)]
#[ORM\Table(name: 'tenants')]
class Tenant extends DefaultEntity
{

    #[ORM\Column(name: 'name', type: 'string', nullable: false)]
    protected ?string $name = null;

    #[ORM\Column(name: 'api_key', type: 'string', length: 128, unique: true)]
    private string $apiKey;

    #[ORM\Column(name: 'webhook_key', type: 'string', length: 128)]
    private string $webhookKey;

    #[ORM\Column(name: 'webhook_url', type: 'string')]
    private string $webhookUrl;

    #[ORM\Column(name: 'subscribed_events', type: 'json', nullable: true)]
    private ?array $subscribedEvents = [];

    #[ORM\Column(name: 'active', type: 'boolean', options: ['default' => 1])]
    protected bool $active = true;

    /**
     * @var Collection<int, TenantWebhook>|TenantWebhook[]
     */
    #[ORM\OneToMany(mappedBy: 'tenant', targetEntity: TenantWebhook::class, cascade: [
        "persist",
        "remove"
    ], orphanRemoval: true)]
    protected Collection|array $webhooks;

    #[ORM\Column(name: "created_at", type: "datetime", nullable: false, options: ["default" => "CURRENT_TIMESTAMP"])]
    protected \DateTime $createdAt;

    #[ORM\Column(type: "json", nullable: true)]
    protected ?array $automaticTasks = null;

    public function __construct()
    {
        parent::__construct();

        $this->webhooks = new ArrayCollection();
    }

    public function __toString()
    {
        return (string)$this->name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function getWebhookKey(): string
    {
        return $this->webhookKey;
    }

    public function setWebhookKey(string $webhookKey): void
    {
        $this->webhookKey = $webhookKey;
    }

    public function getWebhookUrl(): string
    {
        return $this->webhookUrl;
    }

    public function setWebhookUrl(string $webhookUrl): void
    {
        $this->webhookUrl = $webhookUrl;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getSubscribedEvents(): array
    {
        return (array)$this->subscribedEvents;
    }

    public function setSubscribedEvents(array $subscribedEvents): void
    {
        $this->subscribedEvents = $subscribedEvents;
    }

    public function getWebhooks(): Collection|array
    {
        return $this->webhooks;
    }

    public function setWebhooks(Collection|array $webhooks): void
    {
        $this->webhooks = $webhooks;
    }

    public function addWebhook(TenantWebhook $webhook): void
    {
        $webhook->setTenant($this);
        if (!$this->webhooks->contains($webhook)) {
            $this->webhooks->add($webhook);
        }
    }

    public function supportsWebhook(string $name): bool
    {
        return $this->webhooks->filter(fn(TenantWebhook $webhook) => $webhook->getName() === $name)->count() > 0;
    }

    public function getWebhookByName(string $name): ?TenantWebhook
    {
        return $this->webhooks->filter(fn(TenantWebhook $webhook) => $webhook->getName() === $name)->first() ?: null;
    }

    public function isSubscribedToEvent(string $event): bool
    {
        return in_array($event, (array)$this->subscribedEvents);
    }

    public function getAutomaticTasks(): ?array
    {
        return $this->automaticTasks;
    }
    public function getAutomaticTasksString(): ?string
    {
        if($this->automaticTasks === null) {
            return null;
        }

        return json_encode($this->automaticTasks);
    }


    public function setAutomaticTasks(?array $automaticTasks): void
    {
        $this->automaticTasks = $automaticTasks;
    }

    public function setAutomaticTasksString(?string $automaticTasks): void
    {
        $this->automaticTasks = json_decode($automaticTasks, true);

    }


}
