<?php

namespace Horeca\MiddlewareClientBundle\VO\Horeca;

use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class ServiceCredentials
{

    #[Serializer\SerializedName("base_url")]
    #[Assert\NotNull(message: "app.parameter.base_url.not_null")]
    public ?string $baseUrl = null;

    #[Serializer\SerializedName("api_key")]
    #[Assert\NotNull(message: "app.parameter.api_key.not_null")]
    public ?string $apiKey = null;

    #[Serializer\SerializedName("team_id")]
    #[Assert\NotNull(message: "app.parameter.team_id.not_null")]
    public ?string $teamId = null;

    #[Serializer\SerializedName("restaurant_slug")]
    #[Assert\NotNull(message: "app.parameter.restaurant_slug.not_null")]
    public ?string $restaurantSlug = null;

}
