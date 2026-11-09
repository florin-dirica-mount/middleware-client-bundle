<?php

namespace Horeca\MiddlewareClientBundle\VO\Horeca;

use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Great Food asks whether an order it sent here can still be cancelled (POST /api/order/cancel/check), before showing
 * the cancellation form. Read only: nothing changes at the provider.
 */
class HorecaCheckOrderCancellationBody
{
    /** The Great Food order id, as sent in the order (`cart.id`). */
    #[Serializer\SerializedName("tenant_order_id")]
    #[Assert\NotBlank(message: "app.parameter.tenant_order_id.not_blank")]
    public string $tenantOrderId;

    #[Serializer\SerializedName("service_credentials")]
    public ?array $providerCredentials = null;
}
