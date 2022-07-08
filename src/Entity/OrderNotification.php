<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="Horeca\MiddlewareClientBundle\Repository\OrderNotificationRepository")
 * @ORM\Table(name="hmc_order_notifications", indexes={
 *          @ORM\Index(name="hmc_order_notifications_created_at_status_idx", columns={"created_at", "status"}),
 *          @ORM\Index(name="hmc_order_notifications_horeca_order_id_idx", columns={"horeca_order_id"})
 *     })
 */
class OrderNotification extends AbstractEntity
{
    const STATUS_RECEIVED = 'received';     // order is received from the source system and it's products can be mapped
    const STATUS_PENDING = 'pending';       // order has products mapped as is ready to be sent to the target system
    const STATUS_NOTIFIED = 'notified';     // target system was notified with the order
    const STATUS_FAILED = 'failed';         // an error occurred during processing of this order, at any step

    /**
     * Order ID in the source system
     *
     * @ORM\Column(name="horeca_order_id", type="string", length=36)
     */
    private string $horecaOrderId;

    /**
     * Order ID in the target system
     *
     * @ORM\Column(name="service_order_id", type="string", length=36, nullable=true)
     */
    private string $serviceOrderId;

    /**
     * Notification status, whether the target system was notified with this order or not
     *
     * @ORM\Column(name="status", type="string", length=50, options={"default":"received"})
     */
    private string $status;

    /**
     * @ORM\Column(name="restaurant_id", type="string", length=36, nullable=true)
     */
    private string $restaurantId;

    /**
     * @ORM\Column(name="service_credentials", type="json", nullable=true)
     */
    private string $serviceCredentials;

    /**
     * Data received from Horeca platform
     *
     * @ORM\Column(name="horeca_payload", type="json")
     */
    private string $horecaPayload;

    /**
     * Data that needs to be sent to target service
     *
     * @ORM\Column(name="service_payload", type="json", nullable=true)
     */
    private ?string $servicePayload = null;

    /**
     * Response received from the target system for this notification
     *
     * @ORM\Column(name="service_response", type="json", nullable=true)
     */
    private ?string $responsePayload = null;

    /**
     * If an error happens it's message should be written here
     *
     * @ORM\Column(name="error_message", type="text", nullable=true)
     */
    private ?string $errorMessage = null;

    /**
     * Date at which the target system was notified with this order
     *
     * @ORM\Column(name="notified_at", type="datetime", nullable=true)
     */
    private ?\DateTime $notifiedAt = null;

    /**
     * @ORM\OneToMany(targetEntity="Horeca\MiddlewareClientBundle\Entity\OrderStatusEntry", mappedBy="order", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @Serializer\Exclude()
     * @var Collection|OrderStatusEntry[]
     */
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
     * @return string
     */
    public function getHorecaOrderId(): string
    {
        return $this->horecaOrderId;
    }

    /**
     * @param string $horecaOrderId
     */
    public function setHorecaOrderId(string $horecaOrderId): void
    {
        $this->horecaOrderId = $horecaOrderId;
    }

    /**
     * @return string
     */
    public function getServiceOrderId(): string
    {
        return $this->serviceOrderId;
    }

    /**
     * @param string $serviceOrderId
     */
    public function setServiceOrderId(string $serviceOrderId): void
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
     * @return string
     */
    public function getRestaurantId(): string
    {
        return $this->restaurantId;
    }

    /**
     * @param string $restaurantId
     */
    public function setRestaurantId(string $restaurantId): void
    {
        $this->restaurantId = $restaurantId;
    }

    /**
     * @return string
     */
    public function getServiceCredentials(): string
    {
        return $this->serviceCredentials;
    }

    /**
     * @param string $serviceCredentials
     */
    public function setServiceCredentials(string $serviceCredentials): void
    {
        $this->serviceCredentials = $serviceCredentials;
    }

    /**
     * @return string
     */
    public function getHorecaPayload(): string
    {
        return $this->horecaPayload;
    }

    /**
     * @param string $horecaPayload
     */
    public function setHorecaPayload(string $horecaPayload): void
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
     * @return OrderStatusEntry[]|Collection
     */
    public function getStatusEntries(): array|ArrayCollection|Collection
    {
        return $this->statusEntries;
    }

    /**
     * @param OrderStatusEntry[]|Collection $statusEntries
     */
    public function setStatusEntries(array|ArrayCollection|Collection $statusEntries): void
    {
        $this->statusEntries = $statusEntries;
    }

}
