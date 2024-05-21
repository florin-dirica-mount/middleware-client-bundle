<?php

namespace Horeca\MiddlewareClientBundle\Enum;

final class MappingNotificationStatus
{
    /** order is received from the source system and it's products can be mapped  */
    const Received = 'received';

    /** order has products mapped as is ready to be sent to the target system */
    const Pending = 'pending';

    /** order started mapping process */
    const MappingStarted = 'mapping-started';

    /** order has products mapped as is ready to be sent to the target system */
    const Mapped = 'mapped';

    /** order is being sent to the target system */
    const SendingNotification = 'sending-notification';

    /** target system was notified with the order */
    const Notified = 'notified';

    /** conformation is being sent to the source system after target system has confirmed the order */
    const SendingConfirmation = 'sending-notification';

    /** conformation was sent to the source system after target system has confirmed the order */
    const Confirmed = 'confirmed';

    /** an error occurred during processing of this order, at any step */
    const Failed = 'failed';

    /** order is being sent to the target system */
    const Skipped = 'skipped';

    protected function __construct() { }
}
