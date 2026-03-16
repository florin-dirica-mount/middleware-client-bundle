<?php

namespace Horeca\MiddlewareClientBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\ProductNotificationRepositoryDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\MappingLoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProviderApiDI;
use Horeca\MiddlewareClientBundle\Entity\MappingNotification;
use Horeca\MiddlewareClientBundle\Enum\MappingNotificationStatus;
use Horeca\MiddlewareClientBundle\Exception\ProductMappingException;
use Horeca\MiddlewareClientBundle\Message\MappingNotificationMessage;
use Horeca\MiddlewareClientBundle\Message\MessageTransports;
use Horeca\MiddlewareClientBundle\Message\MessageTransportsSync;
use Horeca\MiddlewareClientBundle\Message\Product\MapProviderProductToTenantMessage;
use Horeca\MiddlewareClientBundle\Message\Product\MapProviderProductToTenantSyncMessage;
use Horeca\MiddlewareClientBundle\Message\Product\MapTenantProductToProviderMessage;
use Horeca\MiddlewareClientBundle\Message\Product\MapTenantProductToProviderSyncMessage;
use Horeca\MiddlewareClientBundle\Message\Product\SendProviderProductToTenantMessage;
use Horeca\MiddlewareClientBundle\Message\Product\SendProviderProductToTenantSyncMessage;
use Horeca\MiddlewareClientBundle\Message\Product\SendTenantProductToProviderMessage;
use Horeca\MiddlewareClientBundle\Message\Product\SendTenantProductToProviderSyncMessage;
use Horeca\MiddlewareClientBundle\Service\ProductMapperApiInterface;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Handles product notifications processing
 */
class ProductNotificationMessageHandler implements MessageSubscriberInterface
{
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
        yield MapTenantProductToProviderMessage::class => [
            'method'         => 'handleMapTenantProductToProviderMessage',
            'from_transport' => MessageTransports::MAP_TENANT_PRODUCT_TO_PROVIDER
        ];
        yield MapTenantProductToProviderSyncMessage::class => [
            'method'         => 'handleMapTenantProductToProviderSyncMessage',
            'from_transport' => MessageTransportsSync::SYNC
        ];


        yield SendTenantProductToProviderMessage::class => [
            'method'         => 'handleSendTenantProductToProviderMessage',
            'from_transport' => MessageTransports::SEND_TENANT_PRODUCT_TO_PROVIDER
        ];
        yield SendTenantProductToProviderSyncMessage::class => [
            'method'         => 'handleSendTenantProductToProviderSyncMessage',
            'from_transport' => MessageTransportsSync::SYNC
        ];


        yield MapProviderProductToTenantMessage::class => [
            'method'         => 'handleMapProviderProductToTenantMessage',
            'from_transport' => MessageTransports::MAP_PROVIDER_PRODUCT_TO_TENANT
        ];
        yield MapProviderProductToTenantSyncMessage::class => [
            'method'         => 'handleMapProviderProductToTenantSyncMessage',
            'from_transport' => MessageTransportsSync::SYNC
        ];

        yield SendProviderProductToTenantMessage::class => [
            'method'         => 'handleSendProviderProductToTenantMessage',
            'from_transport' => MessageTransports::SEND_PROVIDER_PRODUCT_TO_TENANT
        ];
        yield SendProviderProductToTenantSyncMessage::class => [
            'method'         => 'handleSendProviderProductToTenantSyncMessage',
            'from_transport' => MessageTransportsSync::SYNC
        ];


    }

    public function handleMapTenantProductToProviderMessageBase(MappingNotificationMessage $message, ?bool $sync = false): void
    {

        if (!$notification = $this->productNotificationRepository->find($message->getNotificationId())) {
            return;
        }

        $this->mappingLogger->logMemoryUsage();

        try {
            $notification->changeStatus(MappingNotificationStatus::MappingStarted);
            $this->productNotificationRepository->save($notification);


            if ($this->providerApi instanceof ProductMapperApiInterface) {
                $this->providerApi->mapTenantProductToProvider($notification);
            } else {
                throw new ProductMappingException('Provider API does not support product mapping. Implement ProductMapperApiInterface In ProviderApi');
            }
            // after mapping notification should have provider payload
            if (!$notification->getProviderPayload()) {
                throw new ProductMappingException('Provider payload is empty');
            }

            $notification->changeStatus(MappingNotificationStatus::Mapped);

            $this->productNotificationRepository->save($notification);

            $this->mappingLogger->saveTo($notification, 'ProductNotificationMessageHandler::');

            if (!$sync) {
                $this->messageBus->dispatch(new SendTenantProductToProviderMessage($notification));
            }
        } catch (\Throwable $e) {
            $this->onNotificationException($notification, $e, __METHOD__);
        }
    }

    public function handleMapTenantProductToProviderMessage(MapTenantProductToProviderMessage $message): void
    {
        $this->handleMapTenantProductToProviderMessageBase($message);
    }

    public function handleMapTenantProductToProviderSyncMessage(MapTenantProductToProviderSyncMessage $message): void
    {
        $this->handleMapTenantProductToProviderMessageBase($message, true);
    }


    public function handleSendTenantProductToProviderMessageBase(MappingNotificationMessage $message, $sync = false): void
    {

        $this->mappingLogger->logMemoryUsage();
        $notification = $this->productNotificationRepository->find($message->getNotificationId());

        try {
            if (!$notification->hasStatus(MappingNotificationStatus::Mapped) || empty($notification->getProviderPayload())) {
                $this->mappingLogger->info(__METHOD__, __LINE__, sprintf('Notification %s status is not *mapped*. Action aborted.', $notification->getId()));

                throw new ProductMappingException('Product is not sent to provider.');
            }

            if ($notification->getStatus() !== MappingNotificationStatus::SendingNotification) {
                $notification->changeStatus(MappingNotificationStatus::SendingNotification);
                $this->productNotificationRepository->save($notification);
            }

            $this->mappingLogger->info(__METHOD__, __LINE__, 'Sending Product to provider...');

            if ($this->providerApi instanceof ProductMapperApiInterface) {
                $notification = $this->providerApi->sendTenantProductToProvider($notification);
            } else {
                throw new ProductMappingException('Provider API does not support Product mapping. Implement ProductMapperApiInterface In ProviderApi');
            }

            $notification->setNotifiedAt(new \DateTime());
//            if request succeeded clear provider body
            $notification->changeStatus(MappingNotificationStatus::Notified);

            $this->mappingLogger->info(__METHOD__, __LINE__, sprintf('Product %s sent to provider with id %s', $notification->getId(), $notification->getProviderObjectId()));

            $this->productNotificationRepository->save($notification);

            $this->mappingLogger->saveTo($notification, 'handleSendTenantProductToProviderMessageBase::');

        } catch (\Throwable $e) {
            $this->onNotificationException($notification, $e, __METHOD__);
        }

    }

    public function handleSendTenantProductToProviderMessage(SendTenantProductToProviderMessage $message): void
    {
        $this->handleSendTenantProductToProviderMessageBase($message);
    }

    public function handleSendTenantProductToProviderSyncMessage(SendTenantProductToProviderSyncMessage $message): void
    {
        $this->handleSendTenantProductToProviderMessageBase($message, true);
    }


    public function handleMapProviderProductToTenantMessageBase(MappingNotificationMessage $message, ?bool $sync = false): void
    {

        if (!$notification = $this->productNotificationRepository->find($message->getNotificationId())) {
            return;
        }

        $this->mappingLogger->logMemoryUsage();

        try {
            $notification->changeStatus(MappingNotificationStatus::MappingStarted);
            $this->productNotificationRepository->save($notification);


            if ($this->providerApi instanceof ProductMapperApiInterface) {
                $this->providerApi->mapProviderProductToTenant($notification);
            } else {
                throw new ProductMappingException('Provider API does not support Product mapping. Implement ProductMapperApiInterface In ProviderApi');
            }
            // after mapping notification should have provider payload
            if (!$notification->getTenantPayload()) {
                throw new ProductMappingException('Tenant payload is empty');
            }

            $notification->changeStatus(MappingNotificationStatus::Mapped);

            $this->productNotificationRepository->save($notification);

            $this->mappingLogger->saveTo($notification, 'ProductNotificationMessageHandler::');

            if (!$sync) {
                $this->messageBus->dispatch(new SendProviderProductToTenantMessage($notification));
            }
        } catch (\Throwable $e) {
            $this->onNotificationException($notification, $e, __METHOD__);
        }
    }

    public function handleMapProviderProductToTenantMessage(MapProviderProductToTenantMessage $message): void
    {
        $this->handleMapProviderProductToTenantMessageBase($message);
    }

    public function handleMapProviderProductToTenantSyncMessage(MapProviderProductToTenantSyncMessage $message): void
    {
        $this->handleMapProviderProductToTenantMessageBase($message, true);
    }


    public function handleSendProviderProductToTenantMessageBase(MappingNotificationMessage $message, $sync = false): void
    {

        $this->mappingLogger->logMemoryUsage();
        $notification = $this->productNotificationRepository->find($message->getNotificationId());

        try {
            if (!$notification->hasStatus(MappingNotificationStatus::Mapped) || empty($notification->getTenantPayload())) {
                $this->mappingLogger->info(__METHOD__, __LINE__, sprintf('Notification %s status is not *mapped*. Action aborted.', $notification->getId()));

                throw new ProductMappingException('Product is not sent to tenant.');
            }

            if ($notification->getStatus() !== MappingNotificationStatus::SendingNotification) {
                $notification->changeStatus(MappingNotificationStatus::SendingNotification);
                $this->productNotificationRepository->save($notification);
            }

            $this->mappingLogger->info(__METHOD__, __LINE__, 'Sending Product to tenant...');

            if ($this->providerApi instanceof ProductMapperApiInterface) {
                $notification = $this->providerApi->sendProviderProductToTenant($notification);
            } else {
                throw new ProductMappingException('Provider API does not support Product mapping. Implement ProductMapperApiInterface In ProviderApi');
            }

            $notification->setNotifiedAt(new \DateTime());
//            if request succeeded clear provider body
            $notification->changeStatus(MappingNotificationStatus::Notified);

            $this->mappingLogger->info(__METHOD__, __LINE__, sprintf('Product %s sent to provider with id %s', $notification->getId(), $notification->getProviderObjectId()));

            $this->productNotificationRepository->save($notification);

            $this->mappingLogger->saveTo($notification, 'handleSendProviderProductToTenantMessageBase::');

        } catch (\Throwable $e) {
            $this->onNotificationException($notification, $e, __METHOD__);
        }

    }

    public function handleSendProviderProductToTenantMessage(SendProviderProductToTenantMessage $message): void
    {
        $this->handleSendProviderProductToTenantMessageBase($message);
    }

    public function handleSendProviderProductToTenantSyncMessage(SendProviderProductToTenantSyncMessage $message): void
    {
        $this->handleSendProviderProductToTenantMessageBase($message, true);
    }


    protected function onNotificationException(MappingNotification $notification, \Throwable $e, $method): void
    {
        $this->mappingLogger->error(__METHOD__, __LINE__, $e->getMessage());

        $notification->changeStatus(MappingNotificationStatus::Failed);
        $notification->setErrorMessage($e->getMessage());

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        $this->mappingLogger->logMemoryUsage();
        $this->mappingLogger->saveTo($notification, 'ProductNotificationMessageHandler::' . $method);
    }

}
