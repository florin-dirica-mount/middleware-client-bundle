<?php

namespace App\VO;

use JMS\Serializer\Annotation as Serializer;

class Place
{
    #[Serializer\SerializedName("name")]
    #[Serializer\Type("string")]
    public ?string $name = null;
    #[Serializer\SerializedName("contact_phone_number")]
    #[Serializer\Type("string")]
    public ?string $contactPhoneNumber = null;
    #[Serializer\SerializedName("contact_name")]
    #[Serializer\Type("string")]
    public ?string $contactName = null;

    #[Serializer\SerializedName("address")]
    #[Serializer\Type("string")]
    public string $address;
    #[Serializer\SerializedName("coordinates")]
    #[Serializer\Type(Coordiantes::class)]
    public ?Coordiantes $coordinates = null;

}
