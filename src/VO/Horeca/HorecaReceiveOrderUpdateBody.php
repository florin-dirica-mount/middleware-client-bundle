<?php

namespace Horeca\MiddlewareClientBundle\VO\Horeca;


use Horeca\MiddlewareCommonLib\Constants\ShoppingCartUpdateEvents;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class HorecaReceiveOrderUpdateBody extends BaseHorecaReceiveOrderBody
{

    #[Serializer\SerializedName("event_type")]
    #[Serializer\Type("string")]
    #[Assert\Choice(choices: ShoppingCartUpdateEvents::EVENTS_ARRAY, message: "Invalid event type")]

    public string $eventType;

    /* ex: Horeca\MiddlewareCommonLib\Constants\ShoppingCartUpdateEvents */

}
