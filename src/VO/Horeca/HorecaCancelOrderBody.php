<?php

namespace Horeca\MiddlewareClientBundle\VO\Horeca;

use Horeca\MiddlewareCommonLib\Constants\OrderCancellationReason;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Great Food asks the middleware to cancel an order it sent there (POST /api/order/cancel). What "cancel" means is the
 * middleware's business (a delivery, an order in a POS, ...), and so is whether it is still allowed.
 */
class HorecaCancelOrderBody
{
    /** The Great Food order id, as sent in the order (`cart.id`). */
    #[Serializer\SerializedName("tenant_order_id")]
    #[Assert\NotBlank(message: "app.parameter.tenant_order_id.not_blank")]
    public string $tenantOrderId;

    /** One of OrderCancellationReason. */
    #[Serializer\SerializedName("reason")]
    #[Assert\NotBlank(message: "app.parameter.reason.not_blank")]
    #[Assert\Choice(choices: OrderCancellationReason::ALL, message: "app.parameter.reason.invalid")]
    public string $reason;

    /** Free text from whoever cancelled, for the logs. */
    #[Serializer\SerializedName("comment")]
    #[Assert\Length(max: 255)]
    public ?string $comment = null;

    /** Who cancelled, for the logs (e.g. the Great Food user's name). */
    #[Serializer\SerializedName("cancelled_by")]
    #[Assert\Length(max: 255)]
    public ?string $cancelledBy = null;

    #[Serializer\SerializedName("service_credentials")]
    public ?array $providerCredentials = null;
}
