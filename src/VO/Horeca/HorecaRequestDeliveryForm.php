<?php

namespace Horeca\MiddlewareClientBundle\VO\Horeca;

use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class HorecaRequestDeliveryForm
{

    #[Serializer\SerializedName("delivery_request_id")]
    public $deliveryRequestId;

    #[Serializer\SerializedName("external_id")]
    public ?string $externalId = null;

    #[Serializer\SerializedName('pickup_starting_with')]
    #[Assert\NotNull(message: "app.parameter.pickup_starting_with.not_null")]
    /** @var $pickupStartingWith \DateTime<'Y-m-d H:i:s','Europe/Bucharest'> */
    public \DateTime $pickupStartingWith;

    #[Serializer\SerializedName('deliver_before')]
    /** @var $deliverBefore \DateTime<'Y-m-d H:i:s','Europe/Bucharest'> */
    public ?\DateTime $deliverBefore = null;

    #[Serializer\SerializedName("pick_up_place")]
    #[Assert\NotNull(message: "app.parameter.pick_up_place.not_null")]
    public Place $pickUpPlace;

    #[Serializer\SerializedName("drop_off_place")]
    public ?Place $dropOffPlace;
    #[Serializer\SerializedName("delivery_cost")]
    public ?int $deliveryCost = null;

    #[Serializer\SerializedName("package_value")]
    public string|float|int|null $packageValue = null;

    #[Serializer\SerializedName("comment")]
    public ?string $comment = null;

    #[Serializer\SerializedName("image")]
    public ?string $image = null;

    #[Serializer\SerializedName("base64Image")]
    public ?string $base64Image = null;

    #[Serializer\SerializedName('payment_type')]
    public ?string $paymentType = null;
    #[Serializer\SerializedName('delivery_code')]
    public ?string $deliveryCode = null;

}
