<?php

namespace Horeca\MiddlewareClientBundle\Controller;

use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\OrderNotificationRepositoryDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\TenantRepositoryDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProtocolActionsServiceDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProviderApiDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\TenantServiceDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Enum\OrderNotificationSource;
use Horeca\MiddlewareClientBundle\Enum\OrderNotificationType;
use Horeca\MiddlewareClientBundle\Enum\SerializationGroups;
use Horeca\MiddlewareClientBundle\Event\TenantOrderEvent;
use Horeca\MiddlewareClientBundle\Exception\ApiException;
use Horeca\MiddlewareClientBundle\Message\MapTenantOrderToProviderMessage;
use Horeca\MiddlewareClientBundle\Repository\OrderNotificationRepository;
use Horeca\MiddlewareClientBundle\VO\Api\OrderNotificationResponseDataDto;
use Horeca\MiddlewareClientBundle\VO\Horeca\HorecaInitializeShopBody;
use Horeca\MiddlewareClientBundle\VO\Horeca\HorecaRequestDeliveryBody;
use Horeca\MiddlewareClientBundle\VO\Horeca\HorecaSendOrderBody;
use Horeca\MiddlewareCommonLib\Exception\HorecaException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class HorecaApiController extends AbstractController
{
    use OrderNotificationRepositoryDI;
    use TenantRepositoryDI;
    use ProviderApiDI;
    use ProtocolActionsServiceDI;
    use TenantServiceDI;

    public function __construct(protected SerializerInterface      $serializer,
                                protected LoggerInterface          $logger,
                                protected ValidatorInterface       $validator,
                                protected TranslatorInterface      $translator,
                                protected EventDispatcherInterface $eventDispatcher)
    {
    }

    public function requestDelivery(Request $request): Response
    {
        try {
            /** @var HorecaRequestDeliveryBody $body */
            $body = $this->deserializeRequestBody($request, HorecaSendOrderBody::class);
            $tenant = $this->protocolActionsService->authorizeTenant($request);
            $credentials = $this->tenantService->compileTenantCredentials($tenant, $body->providerCredentials);

            $errors = $this->validateObject($body->form);
            if (count($errors) > 0) {
                return new Response(json_encode($errors));
            }

            if (!$this->providerApi->requestDelivery($body, $credentials)) {
                return new JsonResponse(['success' => false], Response::HTTP_BAD_REQUEST);
            }

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function viewNotification(Request $request): Response
    {
        try {
            if (!$notificationId = $request->query->get('id')) {
                throw new HorecaException('Missing notification ID');
            }

            if (!$notification = $this->orderNotificationRepository->find($notificationId)) {
                throw new HorecaException('Notification not found');
            }

            if ($url = $this->providerApi->generateNotificationViewUrl($notification)) {
                return $this->redirect($url);
            }

            return new JsonResponse([
                'status' => $notification->getStatus()
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function sendOrder(Request                     $request,
                              MessageBusInterface         $messageBus,
                              OrderNotificationRepository $orderNotificationRepository): Response
    {
        try {
            /** @var HorecaSendOrderBody $body */
            $body = $this->deserializeRequestBody($request, HorecaSendOrderBody::class);
            $tenant = $this->protocolActionsService->authorizeTenant($request);

            $order = $orderNotificationRepository->findOneByTenantOrderId($tenant, $body->cart->getId());
            $dispatchMessage = false;
            if (!$order) {
                $this->logger->info(sprintf('[%s.%d] New order received: %s', __METHOD__, __LINE__, $body->cart->getId()));

                $order = new OrderNotification();
                $order->setType(OrderNotificationType::NewOrder);
                $order->setSource(OrderNotificationSource::Tenant);

                $dispatchMessage = true;
            } else {
                $order->setType(OrderNotificationType::OrderUpdate);
            }

            $order->setTenant($tenant);
            $order->setTenantObjectId($body->cart->getId());
            $order->setHorecaPayloadString($this->serializer->serialize($body->cart, 'json'));
            $order->setTenantShopId($body->cart->getRestaurant()->getId());

            if ($body->providerCredentials) {
                $order->setServiceCredentials($body->providerCredentials);
            }

            $this->orderNotificationRepository->save($order);

            $order->setViewUrl(
                $this->generateUrl('horeca_api_notification_view', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            );
            $this->orderNotificationRepository->save($order);

            $this->eventDispatcher->dispatch(new TenantOrderEvent($order), TenantOrderEvent::ORDER_RECEIVED);

            if ($dispatchMessage) {
                $messageBus->dispatch(new MapTenantOrderToProviderMessage($order));
            }

            $context = SerializationContext::create()->setGroups([SerializationGroups::TenantOrderNotificationView]);
            $data = $this->serializer->serialize(new OrderNotificationResponseDataDto($order), 'json', $context);

            return new JsonResponse($data, 200, [], true);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function initializeShop(Request $request): Response
    {
        try {
            /** @var HorecaInitializeShopBody $body */
            $body = $this->deserializeRequestBody($request, HorecaInitializeShopBody::class);
            $tenant = $this->protocolActionsService->authorizeTenant($request);

            if (!$this->providerApi->initializeShop($tenant, $body->tenantShopId, $body->providerShopId)) {
                return new JsonResponse(['success' => false], Response::HTTP_BAD_REQUEST);
            }

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @throws ApiException
     */
    private function deserializeRequestBody(Request $request, string $type): object
    {
        $body = $this->deserializeObject($request->getContent(), $type);

        $errors = $this->validateObject($body);
        if (count($errors) > 0) {
            throw new ApiException(json_encode($errors));
        }

        return $body;
    }

    private function deserializeObject(string $json, string $type): object
    {
        return $this->serializer->deserialize($json, $type, 'json');
    }

    private function validateObject(object $object): array
    {
        $errorMessages = [];
        $errors = $this->validator->validate($object);
        if (count($errors) > 0) {

            foreach ($errors as $violation) {
                $errorMessages[] = $this->translator->trans($violation->getMessage(), [], 'validators');
            }
        }

        return $errorMessages;
    }

    private function handleException(\Exception $e): JsonResponse
    {
        $this->logger->error(sprintf('[%s] %s', __METHOD__, $e->getMessage()));
        $this->logger->error(sprintf('[%s] %s', __METHOD__, $e->getTraceAsString()));

        $data = [
            'success' => false,
            'message' => $e->getMessage(),
        ];

        $env = $this->getParameter('kernel.environment');
        if ($env === 'test') {
            $data['trace'] = $e->getTraceAsString();
        }

        $code = ($e instanceof HorecaException || $e instanceof ApiException)
            ? Response::HTTP_BAD_REQUEST
            : Response::HTTP_INTERNAL_SERVER_ERROR;

        return new JsonResponse($data, $code);
    }
}
