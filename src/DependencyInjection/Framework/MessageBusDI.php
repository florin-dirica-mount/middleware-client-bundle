<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Framework;

use Symfony\Component\Messenger\MessageBusInterface;

trait MessageBusDI
{
    protected MessageBusInterface $messageBus;

    /**
     * @required
     */
    public function setMessageBus(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }
}
