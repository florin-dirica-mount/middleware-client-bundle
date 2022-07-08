<?php

namespace App\VO\Horeca;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class ServiceCredentials
{

    #[Serializer\SerializedName("base_url")]
    #[Serializer\Type("string")]
    #[Assert\NotNull(message: "app.parameter.base_url.not_null")]
    public ?string $baseUrl = null;

    #[Serializer\SerializedName("api_key")]
    #[Serializer\Type("string")]
    #[Assert\NotNull(message: "app.parameter.api_key.not_null")]
    public ?string $apiKey = null;

    #[Serializer\SerializedName("team_id")]
    #[Serializer\Type("string")]
    #[Assert\NotNull(message: "app.parameter.team_id.not_null")]
    public ?string $teamId = null;

    #[Serializer\SerializedName("restaurant_slug")]
    #[Serializer\Type("string")]
    #[Assert\NotNull(message: "app.parameter.restaurant_slug.not_null")]
    public ?string $restaurantSlug = null;

}
