<?php

namespace Horeca\MiddlewareClientBundle\VO\Horeca;

use Symfony\Component\Serializer\Attribute as Serializer;

class Place
{
    #[Serializer\SerializedName("horeca_id")]
    public ?string $horecaId = null;

    #[Serializer\SerializedName("name")]
    public ?string $name = null;
    #[Serializer\SerializedName("contact_phone_number")]
    public ?string $contactPhoneNumber = null;
    #[Serializer\SerializedName("contact_name")]
    public ?string $contactName = null;

    #[Serializer\SerializedName("address")]
    public string $address;
    #[Serializer\SerializedName("coordinates")]
    public ?Coordiantes $coordinates = null;

}
