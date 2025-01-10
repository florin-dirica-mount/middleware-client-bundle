<?php

namespace Horeca\MiddlewareClientBundle\VO\Horeca;


use JMS\Serializer\Annotation as Serializer;

class HorecaReceiveOrderUpdateBody extends BaseHorecaReceiveOrderBody
{

    #[Serializer\SerializedName("event_type")]
    #[Serializer\Type("string")]
    public string $eventType;

    /* ex: Horeca\MiddlewareCommonLib\Constants\ShoppingCartUpdateEvents */

}
