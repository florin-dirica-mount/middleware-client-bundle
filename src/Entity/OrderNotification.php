<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\Repository\OrderNotificationRepository;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: OrderNotificationRepository::class)]
#[ORM\Table(name: "hmc_order_notifications")]
#[ORM\Index(columns: ["created_at", "status"], name: "hmc_order_notifications_created_at_status_idx")]
#[ORM\Index(columns: ["horeca_order_id", "type"], name: "hmc_order_notifications_type_idx")]
#[ORM\Index(columns: ["service_order_id", "type"], name: "hmc_order_notifications_service_order_id_type_idx")]
#[ORM\Index(columns: ["restaurant_id", "type"], name: "hmc_order_notifications_restaurant_id_type_idx")]
#[ORM\Index(columns: ["horeca_order_id"], name: "hmc_order_notifications_horeca_order_id_idx")]
class OrderNotification extends AbstractEntity
{
    const STATUS_RECEIVED = 'received';     // order is received from the source system and it's products can be mapped
    const STATUS_PENDING = 'pending';       // order has products mapped as is ready to be sent to the target system
    const STATUS_NOTIFIED = 'notified';     // target system was notified with the order
    const STATUS_CONFIRMED = 'confirmed';   // target system has confirmed the order
    const STATUS_FAILED = 'failed';         // an error occurred during processing of this order, at any step


    const TYPE_NEW_ORDER = 'new-order';
    const TYPE_ORDER_UPDATE = 'order-update';

    const SOURCE_HORECA = 'horeca';
    const SOURCE_EXTERNAL_SERVICE = 'external-service';

    #[ORM\Column(name: "horeca_order_id", type: "string", length: 36, nullable: true)]
    private ?string $horecaOrderId;

    #[ORM\Column(name: "service_order_id", type: "string", length: 36, nullable: true)]
    private ?string $serviceOrderId;

    #[ORM\Column(name: "status", type: "string", length: 50, nullable: false, options: ["default" => "received"])]
    private string $status;

    #[ORM\Column(name: "type", type: "string", length: 50, nullable: false, options: ["default" => "new-order"])]
    private string $type;

    #[ORM\Column(name: "source", type: "string", length: 50, nullable: true)]
    private ?string $source;

    #[ORM\Column(name: "restaurant_id", type: "string", length: 36, nullable: true)]
    private ?string $restaurantId;

    #[ORM\Column(name: "service_credentials", type: "json", nullable: true)]
    private ?string $serviceCredentials;

    #[ORM\Column(name: "horeca_payload", type: "json", nullable: true)]
    private ?string $horecaPayload = null;

    #[ORM\Column(name: "service_payload", type: "json", nullable: true)]
    private ?string $servicePayload = null;

    #[ORM\Column(name: "response_payload", type: "json", nullable: true)]
    private ?string $responsePayload = null;

    #[ORM\Column(name: "error_message", type: "text", nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column(name: "notified_at", type: "datetime", nullable: true)]
    private ?\DateTime $notifiedAt = null;

    #[ORM\OneToMany(mappedBy: "order", targetEntity: OrderStatusEntry::class, cascade: [
        "persist",
        "remove"
    ], fetch: "EXTRA_LAZY", orphanRemoval: true)]
    #[Serializer\Exclude]
    #[ORM\OrderBy(["createdAt" => "ASC"])]
    /** @var array|Collection|ArrayCollection */
    private array|Collection|ArrayCollection $statusEntries;

    public function __construct()
    {
        parent::__construct();
        $this->statusEntries = new ArrayCollection();
        $this->changeStatus(OrderNotification::STATUS_RECEIVED);
    }

    public function __toString()
    {
        return sprintf('%s - %s', $this->horecaOrderId, $this->status);
    }

    /**
     * @param string $status
     */
    public function changeStatus(string $status): void
    {
        $this->status = $status;

        $entry = new OrderStatusEntry();
        $entry->setStatus($status);
        $entry->setOrder($this);
        $this->getStatusEntries()->add($entry);
    }

    /**
     * @return string|null
     */
    public function getHorecaOrderId(): ?string
    {
        return $this->horecaOrderId;
    }

    /**
     * @param string|null $horecaOrderId
     */
    public function setHorecaOrderId(?string $horecaOrderId): void
    {
        $this->horecaOrderId = $horecaOrderId;
    }

    /**
     * @return string|null
     */
    public function getServiceOrderId(): ?string
    {
        return $this->serviceOrderId;
    }

    /**
     * @param string|null $serviceOrderId
     */
    public function setServiceOrderId(?string $serviceOrderId): void
    {
        $this->serviceOrderId = $serviceOrderId;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string|null
     */
    public function getRestaurantId(): ?string
    {
        return $this->restaurantId;
    }

    /**
     * @param string|null $restaurantId
     */
    public function setRestaurantId(?string $restaurantId): void
    {
        $this->restaurantId = $restaurantId;
    }

    /**
     * @return string|null
     */
    public function getServiceCredentials(): ?string
    {
        return $this->serviceCredentials;
    }

    /**
     * @param string|null $serviceCredentials
     */
    public function setServiceCredentials(?string $serviceCredentials): void
    {
        $this->serviceCredentials = $serviceCredentials;
    }

    /**
     * @return string|null
     */
    public function getHorecaPayload(): ?string
    {
        return $this->horecaPayload;
    }

    /**
     * @param string|null $horecaPayload
     */
    public function setHorecaPayload(?string $horecaPayload): void
    {
        $this->horecaPayload = $horecaPayload;
    }

    /**
     * @return string|null
     */
    public function getServicePayload(): ?string
    {
        return $this->servicePayload;
    }

    /**
     * @param string|null $servicePayload
     */
    public function setServicePayload(?string $servicePayload): void
    {
        $this->servicePayload = $servicePayload;
    }

    /**
     * @return string|null
     */
    public function getResponsePayload(): ?string
    {
        return $this->responsePayload;
    }

    /**
     * @param string|null $responsePayload
     */
    public function setResponsePayload(?string $responsePayload): void
    {
        $this->responsePayload = $responsePayload;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * @param string|null $errorMessage
     */
    public function setErrorMessage(?string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return \DateTime|null
     */
    public function getNotifiedAt(): ?\DateTime
    {
        return $this->notifiedAt;
    }

    /**
     * @param \DateTime|null $notifiedAt
     */
    public function setNotifiedAt(?\DateTime $notifiedAt): void
    {
        $this->notifiedAt = $notifiedAt;
    }

    /**
     * @return array|ArrayCollection|Collection
     */
    public function getStatusEntries(): ArrayCollection|Collection|array
    {
        return $this->statusEntries;
    }

    /**
     * @param array|ArrayCollection|Collection $statusEntries
     */
    public function setStatusEntries(ArrayCollection|Collection|array $statusEntries): void
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


}
