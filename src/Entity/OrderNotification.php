<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\Entity\Log\OrderLog;
use Horeca\MiddlewareClientBundle\Entity\Traits\ProviderObjectId;
use Horeca\MiddlewareClientBundle\Entity\Traits\TenantObjectId;
use Horeca\MiddlewareClientBundle\Enum\OrderNotificationStatus;
use Horeca\MiddlewareClientBundle\Enum\OrderNotificationType;
use Horeca\MiddlewareClientBundle\Enum\SerializationGroups;
use Horeca\MiddlewareClientBundle\Repository\OrderNotificationRepository;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: OrderNotificationRepository::class)]
#[ORM\Table(name: "hmc_order_notifications")]
#[ORM\Index(columns: ["created_at", "status"], name: "hmc_order_notifications_created_at_status_idx")]
#[ORM\Index(columns: ["horeca_order_id", "type"], name: "hmc_order_notifications_type_idx")]
#[ORM\Index(columns: ["service_order_id", "type"], name: "hmc_order_notifications_service_order_id_type_idx")]
#[ORM\Index(columns: ["restaurant_id", "type"], name: "hmc_order_notifications_restaurant_id_type_idx")]
#[ORM\Index(columns: ["horeca_order_id"], name: "hmc_order_notifications_horeca_order_id_idx")]
class OrderNotification extends TenantAwareEntity
{
    use TenantObjectId;
    use ProviderObjectId;

    /**
     * @deprecated use getTenantObjectId
     */
    #[ORM\Column(name: "horeca_order_id", type: "string", length: 36, nullable: true)]
    #[Serializer\SerializedName("tenantObjectId")]
//    #[Serializer\Groups([SerializationGroups::TenantOrderNotificationView])]
    private ?string $horecaOrderId = null;

    /**
     * @deprecated use getProviderObjectId
     */
    #[ORM\Column(name: "service_order_id", type: "string", length: 36, nullable: true)]
    #[Serializer\SerializedName("providerObjectId")]
//    #[Serializer\Groups([SerializationGroups::TenantOrderNotificationView])]
    private ?string $serviceOrderId = null;

    #[ORM\Column(name: "status", type: "string", length: 50, nullable: false, options: ["default" => "received"])]
    #[Serializer\Groups([SerializationGroups::TenantOrderNotificationView])]
    private string $status;

    #[ORM\Column(name: "type", type: "string", length: 50, nullable: false, options: ["default" => "new-order"])]
    private string $type;

    #[ORM\Column(name: "source", type: "string", length: 50, nullable: true)]
    private ?string $source = null;

    #[ORM\Column(name: "restaurant_id", type: "string", length: 36, nullable: true)]
    private ?string $tenantShopId = null;

    #[ORM\Column(name: "service_credentials", type: "json", nullable: true)]
    private ?array $serviceCredentials = [];

    #[ORM\Column(name: "horeca_payload", type: "json", nullable: true)]
    private ?array $horecaPayload = null;

    #[ORM\Column(name: "service_payload", type: "json", nullable: true)]
    /**
     * @deprecated use providerPayload
     */
    private ?array $servicePayload = [];
    #[ORM\Column(name: "provider_payload", type: "json", nullable: true)]
    private ?array $providerPayload = [];

    #[ORM\Column(name: "response_payload", type: "json", nullable: true)]
    #[Serializer\Groups([SerializationGroups::TenantOrderNotificationView])]
    private ?array $responsePayload = [];

    #[ORM\Column(name: "view_url", type: "string", nullable: true)]
    #[Serializer\Groups([SerializationGroups::TenantOrderNotificationView])]
    private ?string $viewUrl = null;

    #[ORM\Column(name: "error_message", type: "text", nullable: true)]
    #[Serializer\Groups([SerializationGroups::TenantOrderNotificationView])]
    private ?string $errorMessage = null;

    #[ORM\Column(name: "notified_at", type: "datetime", nullable: true)]
    private ?\DateTime $notifiedAt = null;

    /**
     * @var Collection<int, OrderStatusEntry>|OrderStatusEntry[]
     */
    #[ORM\OneToMany(mappedBy: "order", targetEntity: OrderStatusEntry::class, cascade: [
        "persist",
        "remove"
    ], fetch: "EXTRA_LAZY", orphanRemoval: true)]
    #[Serializer\Exclude]
    #[ORM\OrderBy(["createdAt" => "ASC"])]
    private Collection|array $statusEntries;

    /**
     * @var Collection<int, OrderLog>|OrderLog[]
     */
    #[ORM\OneToMany(mappedBy: "order", targetEntity: OrderLog::class, cascade: ["persist", "remove"], fetch: "EXTRA_LAZY", orphanRemoval: true)]
    #[Serializer\Exclude]
    #[ORM\OrderBy(["createdAt" => "ASC"])]
    private Collection|array $logs;

    public function __construct()
    {
        parent::__construct();

        $this->statusEntries = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->type = OrderNotificationType::NewOrder;
        $this->changeStatus(OrderNotificationStatus::Received);
    }

    public function __toString()
    {
        return sprintf('%s - %s', $this->status, $this->horecaOrderId);
    }

    public function changeStatus(string $status): void
    {
        $this->status = $status;

        $entry = new OrderStatusEntry();
        $entry->setStatus($status);
        $entry->setOrder($this);
        $this->getStatusEntries()->add($entry);
    }

    public function getTruncatedError(int $length = 80): ?string
    {
        if ($this->errorMessage !== null) {
            return substr($this->errorMessage, 0, $length);
        }

        return null;
    }

    /**
     * @deprecated use getTenantObjectId
     */
    public function getHorecaOrderId(): ?string
    {
        return $this->tenantObjectId ?: $this->horecaOrderId;
    }

    /**
     * @deprecated use setTenantObjectId
     */
    public function setHorecaOrderId(?string $horecaOrderId): void
    {
        $this->horecaOrderId = $horecaOrderId;
        $this->tenantObjectId = $horecaOrderId;
    }

    public function setTenantObjectId(string $tenantObjectId): void
    {
        $this->horecaOrderId = $tenantObjectId;
        $this->tenantObjectId = $tenantObjectId;
    }

    /**
     * @deprecated use getProviderObjectId
     */
    public function getServiceOrderId(): ?string
    {
        return $this->providerObjectId ?: $this->serviceOrderId;
    }

    /**
     * @deprecated use setProviderObjectId
     */
    public function setServiceOrderId(?string $serviceOrderId): void
    {
        $this->serviceOrderId = $serviceOrderId;
        $this->providerObjectId = $serviceOrderId;
    }

    public function setProviderObjectId(?string $providerObjectId): void
    {
        $this->serviceOrderId = $providerObjectId;
        $this->providerObjectId = $providerObjectId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string|null
     * @deprecated
     */
    public function getRestaurantId(): ?string
    {
        return $this->tenantShopId;
    }

    /**
     * @param string|null $restaurantId
     * @return void
     * @deprecated
     */
    public function setRestaurantId(?string $restaurantId): void
    {
        $this->tenantShopId = $restaurantId;
    }

    public function getTenantShopId(): ?string
    {
        return $this->tenantShopId;
    }

    public function setTenantShopId(?string $tenantShopId): void
    {
        $this->tenantShopId = $tenantShopId;
    }


    public function getServiceCredentials(): ?array
    {
        return $this->serviceCredentials;
    }

    public function setServiceCredentials(?array $serviceCredentials): void
    {
        $this->serviceCredentials = $serviceCredentials;
    }

    public function getHorecaPayload(): ?array
    {
        return $this->horecaPayload;
    }

    public function setHorecaPayload(array $horecaPayload): void
    {
        $this->horecaPayload = $horecaPayload;
    }

    public function getHorecaPayloadString(): ?string
    {
        return json_encode($this->horecaPayload);
    }

    public function setHorecaPayloadString(string $horecaPayload): void
    {
        $this->horecaPayload = json_decode($horecaPayload, true);
    }

    public function getServicePayload(): ?array
    {
        return $this->servicePayload;
    }

    /**
     * @deprecated use setProviderPayload
     */
    public function setServicePayload(array $servicePayload): void
    {
        $this->servicePayload = $servicePayload;
        $this->providerPayload = $servicePayload;

    }

    public function getServicePayloadString(): ?string
    {
        return json_encode($this->servicePayload);
    }

    public function setServicePayloadString(string $servicePayload): void
    {
        $this->servicePayload = json_decode($servicePayload, true);
    }

    public function getResponsePayload(): ?array
    {
        return $this->responsePayload;
    }

    public function setResponsePayload(array $responsePayload): void
    {
        $this->responsePayload = $responsePayload;
    }

    public function setResponsePayloadString(string $responsePayload): void
    {
        $this->responsePayload = json_decode($responsePayload, true);
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    public function getNotifiedAt(): ?\DateTime
    {
        return $this->notifiedAt;
    }

    public function setNotifiedAt(?\DateTime $notifiedAt): void
    {
        $this->notifiedAt = $notifiedAt;
    }

    /**
     * @return Collection<int, OrderStatusEntry>|OrderStatusEntry[]
     */
    public function getStatusEntries(): Collection|array
    {
        return $this->statusEntries;
    }

    /**
     * @param Collection<int, OrderStatusEntry>|OrderStatusEntry[] $statusEntries
     */
    public function setStatusEntries(Collection|array $statusEntries): void
    {
        $this->statusEntries = $statusEntries;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): void
    {
        $this->source = $source;
    }

    /**
     * @return Collection<int, OrderLog>|OrderLog[]
     */
    public function getLogs(): Collection|array
    {
        return $this->logs;
    }

    /**
     * @param Collection<int, OrderLog>|OrderLog[] $logs
     */
    public function setLogs(Collection|array $logs): void
    {
        $this->logs = $logs;
    }

    public function addLog(OrderLog $log): void
    {
        $log->setOrder($this);
        if (!$this->getLogs()->contains($log)) {
            $this->getLogs()->add($log);
        }
    }

    public function hasStatus(string $status): bool
    {
        return $this->statusEntries->filter(fn(OrderStatusEntry $entry) => $entry->getStatus() === $status)->count() > 0;
    }

    public function getViewUrl(): ?string
    {
        return $this->viewUrl;
    }

    public function setViewUrl(?string $viewUrl): void
    {
        $this->viewUrl = $viewUrl;
    }

    public function getProviderPayload(): ?array
    {
        return $this->providerPayload;
    }

    public function setProviderPayload(?array $providerPayload): void
    {
        $this->providerPayload = $providerPayload;
        $this->servicePayload = $providerPayload;
    }
}
