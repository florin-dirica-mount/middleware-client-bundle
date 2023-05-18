<?php

namespace Horeca\MiddlewareClientBundle\Controller;

use Doctrine\ORM\Exception\ORMException;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\LoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\MessageBusDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\SerializerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\OrderNotificationRepositoryDI;
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
    use MessageBusDI;
    use OrderNotificationRepositoryDI;
    use LoggerDI;
    use SerializerDI;

    #[Rest\Post("/api/order/send", name: "horeca_api_order_send")]
    #[ParamConverter("body", converter: "fos_rest.request_body")]
    public function sendOrder(Request $request, HorecaSendOrderBody $body): Response
    {
        $this->logger->info('[/api/order/send] ' . $request->getContent());

        $this->authorizeRequest($request);

        if (!($body->cart && $body->providerCredentials)) {
            $this->logger->warning('[/api/order/send] ERROR: Missing parameters');

            throw new BadRequestException('Missing parameters: cart, service_credentials!');
        }

        if (!$this->orderNotificationRepository->findOneByHorecaOrderId($body->cart->getId())) {
            $order = new OrderNotification();
            $order->setHorecaOrderId($body->cart->getId());
            $order->setHorecaPayload($this->serializeJson($body->cart));
            $order->setServiceCredentials($this->serializeJson($body->providerCredentials));
            $order->setRestaurantId($body->cart->getRestaurant()->getId());

            try {
                $this->orderNotificationRepository->persist($order);
            } catch (ORMException|\Doctrine\ORM\ORMException $e) {
                $this->logger->critical('[/api/order/send] Exception: ' . $e->getMessage());
                return new JsonResponse(['error' => 'Could not process order. Please retry!', Response::HTTP_UNPROCESSABLE_ENTITY]);
            }
            $this->orderNotificationRepository->flush();

            $this->logger->info('[/api/order/send] Created new order: ' . $order->getId());
            $this->messageBus->dispatch(new OrderNotificationMessage($order));
            $this->logger->info('[/api/order/send] Dispatched new order: ' . $order->getId());
        }

        return new Response();
    }

    protected function authorizeRequest(Request $request)
    {
        if (!$apiKeyHeader = $request->headers->get('Api-Key')) {
            $this->logger->warning('[/api/order/send] ERROR: Missing api key');

            throw new AccessDeniedException();
        }

        if (!$horecaApiKey = $this->getParameter('horeca.shared_key')) {
            $this->logger->critical('[/api/order/send] ERROR: Invalid service configuration!');

            throw new \RuntimeException("Invalid service configuration!");
        }

        if ($apiKeyHeader !== $horecaApiKey) {
            $this->logger->warning('[/api/order/send] ERROR: Invalid api key');

            throw new AccessDeniedException();
        }
    }
}
