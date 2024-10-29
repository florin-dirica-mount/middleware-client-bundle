<?php

namespace Horeca\MiddlewareClientBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\MenuNotificationRepositoryDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\ProductNotificationRepositoryDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\MappingLoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProviderApiDI;
use Horeca\MiddlewareClientBundle\Entity\MappingNotification;
use Horeca\MiddlewareClientBundle\Enum\MappingNotificationStatus;
use Horeca\MiddlewareClientBundle\Exception\MenuMappingException;
use Horeca\MiddlewareClientBundle\Message\MappingNotificationMessage;
use Horeca\MiddlewareClientBundle\Message\Menu\MapTenantMenuToProviderMessage;
use Horeca\MiddlewareClientBundle\Message\Menu\MapTenantMenuToProviderSyncMessage;
use Horeca\MiddlewareClientBundle\Message\Menu\SendTenantMenuToProviderMessage;
use Horeca\MiddlewareClientBundle\Message\Menu\SendTenantMenuToProviderSyncMessage;
use Horeca\MiddlewareClientBundle\Message\MessageTransports;
use Horeca\MiddlewareClientBundle\Message\MessageTransportsSync;
use Horeca\MiddlewareClientBundle\Message\Product\MapTenantProductToProviderMessage;
use Horeca\MiddlewareClientBundle\Message\Product\SendTenantProductToProviderMessage;
use Horeca\MiddlewareClientBundle\Service\MenuMapperApiInterface;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Handles menu notifications processing
 */
class MenuNotificationMessageHandler implements MessageSubscriberInterface
{
    use MenuNotificationRepositoryDI;
    use ProductNotificationRepositoryDI;
    use MappingLoggerDI;
    use ProviderApiDI;

    public function __construct(protected MessageBusInterface    $messageBus,
                                protected EntityManagerInterface $entityManager,
                                protected LoggerInterface        $logger,
                                protected SerializerInterface    $serializer,
    )
    {
    }

    /**
     * @inheritDoc
     */
    public static function getHandledMessages(): iterable
    {
        yield MapTenantMenuToProviderMessage::class => [
            'method'         => 'handleMapTenantMenuToProviderMessage',
            'from_transport' => MessageTransports::MAP_TENANT_MENU_TO_PROVIDER
        ];
        yield MapTenantMenuToProviderSyncMessage::class => [
            'method'         => 'handleMapTenantMenuToProviderSyncMessage',
            'from_transport' => MessageTransportsSync::SYNC
        ];


        yield SendTenantMenuToProviderMessage::class => [
            'method'         => 'handleSendTenantMenuToProviderMessage',
            'from_transport' => MessageTransports::SEND_TENANT_MENU_TO_PROVIDER
        ];
        yield SendTenantMenuToProviderSyncMessage::class => [
            'method'         => 'handleSendTenantMenuToProviderSyncMessage',
            'from_transport' => MessageTransportsSync::SYNC
        ];



    }

    public function handleMapTenantMenuToProviderMessageBase(MappingNotificationMessage $message, ?bool $sync = false): void
    {
        $notification = $this->menuNotificationRepository->find($message->getNotificationId());

        if (!$notification) {
            return;
        }

        $this->mappingLogger->logMemoryUsage();

        try {
            $notification->changeStatus(MappingNotificationStatus::MappingStarted);
            $this->menuNotificationRepository->save($notification);


            if ($this->providerApi instanceof MenuMapperApiInterface) {
                $this->providerApi->mapTenantMenuToProvider($notification);
            } else {
                throw new MenuMappingException('Provider API does not support menu mapping');
            }
            // after mapping notification should have provider payload
            if (!$notification->getProviderPayload()) {
                throw new MenuMappingException('Provider payload is empty');
            }

            $notification->changeStatus(MappingNotificationStatus::Mapped);

            $this->menuNotificationRepository->save($notification);

            if (!$sync) {
                $this->messageBus->dispatch(new SendTenantMenuToProviderMessage($notification));
            }
        } catch (\Throwable $e) {
            $this->onNotificationException($notification, $e, __METHOD__);
        }
    }

    public function handleMapTenantMenuToProviderMessage(MapTenantMenuToProviderMessage $message): void
    {
        $this->handleMapTenantMenuToProviderMessageBase($message);
    }

    public function handleMapTenantMenuToProviderSyncMessage(MapTenantMenuToProviderSyncMessage $message): void
    {
        $this->handleMapTenantMenuToProviderMessageBase($message, true);
    }



    public function handleSendTenantMenuToProviderMessageBase(MappingNotificationMessage $message, $sync = false): void
    {

        $this->mappingLogger->logMemoryUsage();
        $notification = $this->menuNotificationRepository->find($message->getNotificationId());

        try {
            if (!$notification->hasStatus(MappingNotificationStatus::Mapped) || empty($notification->getProviderPayload())) {
                $this->mappingLogger->info(__METHOD__, __LINE__, sprintf('Notification %s status is not *mapped*. Action aborted.', $notification->getId()));

                throw new MenuMappingException('Menu is not sent to provider.');
            }

            if ($notification->getStatus() !== MappingNotificationStatus::SendingNotification) {
                $notification->changeStatus(MappingNotificationStatus::SendingNotification);
                $this->menuNotificationRepository->save($notification);
            }

            $this->mappingLogger->info(__METHOD__, __LINE__, 'Sending menu to provider...');

            if ($this->providerApi instanceof MenuMapperApiInterface) {
                $notification = $this->providerApi->sendTenantMenuToProvider($notification);
            } else {
                throw new MenuMappingException('Provider API does not support menu mapping');
            }

            $notification->setNotifiedAt(new \DateTime());
//            if request succeeded clear provider body
            $notification->changeStatus(MappingNotificationStatus::Notified);

            $this->mappingLogger->info(__METHOD__, __LINE__, sprintf('Menu %s sent to provider with id %s', $notification->getId(), $notification->getProviderObjectId()));

            $this->menuNotificationRepository->save($notification);

        } catch (\Throwable $e) {
            $this->onNotificationException($notification, $e, __METHOD__);
        }

    }

    public function handleSendTenantMenuToProviderMessage(SendTenantMenuToProviderMessage $message): void
    {
        $this->handleSendTenantMenuToProviderMessageBase($message);
    }

    public function handleSendTenantMenuToProviderSyncMessage(SendTenantMenuToProviderSyncMessage $message): void
    {
        $this->handleSendTenantMenuToProviderMessageBase($message, true);
    }


    protected function onNotificationException(MappingNotification $notification, \Throwable $e, $method): void
    {
        $this->mappingLogger->error(__METHOD__, __LINE__, $e->getMessage());

        $notification->changeStatus(MappingNotificationStatus::Failed);
        $notification->setErrorMessage($e->getMessage());

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        $this->mappingLogger->logMemoryUsage();
        $this->mappingLogger->saveTo($notification, 'MenuNotificationMessageHandler::' . $method);
    }

}
