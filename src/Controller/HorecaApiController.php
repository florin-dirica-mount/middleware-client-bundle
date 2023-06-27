<?php

namespace Horeca\MiddlewareClientBundle\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\EntityManagerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\LoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\MessageBusDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\SerializerDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Message\OrderNotificationMessage;
use Horeca\MiddlewareClientBundle\VO\Horeca\HorecaSendOrderBody;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class HorecaApiController extends AbstractFOSRestController
{
    use LoggerDI;
    use SerializerDI;
    use MessageBusDI;
    use EntityManagerDI;

    #[Rest\Post("/api/order/send", name: "horeca_api_order_send")]
    #[ParamConverter("body", converter: "fos_rest.request_body")]
    public function sendOrder(Request $request, HorecaSendOrderBody $body): Response
    {
        $routeName = $request->attributes->get('_route');

        $this->logger->info('[/api/order/send] ' . $request->getContent());

        $this->authorizeRequest($request);

        if (!($body->cart && $body->providerCredentials)) {
            $this->logger->warning("[$routeName] ERROR: Missing parameters");

            throw new BadRequestException('Missing parameters: cart, service_credentials!');
        }

        $existingOrder = $this->entityManager->getRepository(OrderNotification::class)->findOneByHorecaOrderId($body->cart->getId());
        if ($existingOrder) {
            $this->logger->info("[$routeName] Order already received: " . $existingOrder->getId());

            return new JsonResponse(['success' => true]);
        }

        if (!$this->entityManager->getRepository(OrderNotification::class)->findOneByHorecaOrderId($body->cart->getId())) {
            $order = new OrderNotification();
            $order->setHorecaOrderId($body->cart->getId());
            $order->setHorecaPayload($this->serializeJson($body->cart));
            $order->setServiceCredentials($this->serializeJson($body->providerCredentials));
            $order->setRestaurantId($body->cart->getRestaurant()->getId());

            $this->entityManager->persist($order);
            $this->entityManager->flush();

            $this->logger->info("[$routeName] Created new order: " . $order->getId());
            $this->messageBus->dispatch(new OrderNotificationMessage($order));
            $this->logger->info("[$routeName] Dispatched new order: " . $order->getId());
        }

        return new JsonResponse(['success' => true]);
    }

    protected function authorizeRequest(Request $request)
    {
        $routeName = $request->attributes->get('_route');

        if (!$apiKeyHeader = $request->headers->get('Api-Key')) {
            $this->logger->warning("[$routeName] ERROR: Missing api key");

            throw new AccessDeniedException();
        }

        if (!$horecaApiKey = $this->getParameter('horeca.shared_key')) {
            $this->logger->critical("[$routeName] ERROR: Invalid service configuration!");

            throw new \RuntimeException("Invalid service configuration!");
        }

        if ($apiKeyHeader !== $horecaApiKey) {
            $this->logger->warning("[$routeName] ERROR: Invalid api key");

            throw new AccessDeniedException();
        }
    }
}
