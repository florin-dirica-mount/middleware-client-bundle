<?php

namespace App\VO;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class HorecaRequestDeliveryForm
{

    #[Serializer\SerializedName("external_id")]
    #[Serializer\Type("string")]
    public ?string $externalId = null;

    #[Serializer\Type("DateTime<'Y-m-d H:i:s'>")]
    #[Serializer\SerializedName('pickup_starting_with')]
    #[Assert\NotNull(message: "app.parameter.pickup_starting_with.not_null")]
    public \DateTime $pickupStartingWith;

    #[Serializer\Type("DateTime<'Y-m-d H:i:s'>")]
    #[Serializer\SerializedName('deliver_before')]
    public ?\DateTime $deliverBefore = null;

    #[Serializer\SerializedName("pick_up_place")]
    #[Serializer\Type(Place::class)]
    #[Assert\NotNull(message: "app.parameter.pick_up_place.not_null")]
    public Place $pickUpPlace;

    #[Serializer\SerializedName("drop_off_place")]
    #[Serializer\Type(Place::class)]
    public ?Place $dropOffPlace;
    #[Serializer\SerializedName("delivery_cost")]
    #[Serializer\Type("float")]
    public ?int $deliveryCost = null;

    #[Serializer\SerializedName("package_value")]
    #[Serializer\Type("string")]
    public ?string $packageValue = null;

    #[Serializer\SerializedName("comment")]
    #[Serializer\Type("string")]
    public ?string $comment = null;

    #[Serializer\SerializedName("image")]
    #[Serializer\Type("string")]
    public ?string $image = null;

    #[Serializer\SerializedName("base64Image")]
    #[Serializer\Type("string")]
    public ?string $base64Image = null;

    #[Serializer\SerializedName('payment_type')]
    #[Serializer\Type('string')]
    public ?string $paymentType = null;

}
