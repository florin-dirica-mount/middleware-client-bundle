<?php

namespace Horeca\MiddlewareClientBundle\Controller;

use App\VO\HorecaRequestDeliveryBody;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\EntityManagerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\LoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\MessageBusDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\SerializerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProviderApiDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Message\OrderNotificationMessage;
use Horeca\MiddlewareClientBundle\VO\Horeca\HorecaInitializeShopBody;
use Horeca\MiddlewareClientBundle\VO\Horeca\HorecaSendOrderBody;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class HorecaApiController extends AbstractFOSRestController
{
    use LoggerDI;
    use SerializerDI;
    use MessageBusDI;
    use EntityManagerDI;
    use ProviderApiDI;


    #[Rest\Post("/api/delivery/request", name: "horeca_api_request_delivery")]
    #[ParamConverter("body", converter: "fos_rest.request_body")]
    public function requestDelivery(Request                   $request,
                                    HorecaRequestDeliveryBody $body,
                                    ValidatorInterface        $validator,
                                    TranslatorInterface       $translator,
    ): Response
    {

        $this->authorizeRequest($request);

        $serviceCredentials = $this->serializeJson($body->providerCredentials);

        $credentials = $this->deserializeJson($serviceCredentials, $this->providerApi->getProviderCredentialsClass());

        $errors = $validator->validate($body->form);

        if (count($errors) > 0) {
            $errorsArray = [];

            foreach ($errors as $violation) {
                $errorsArray[] = $translator->trans($violation->getMessage(), [], 'validators');
            }

            return new Response(json_encode($errorsArray));
        }

        try {
            $response = $this->providerApi->requestDelivery($body, $credentials);
            if ($response) {
                return new JsonResponse(['success' => true]);
            } else {
                return new JsonResponse(['success' => false], Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
            return new JsonResponse(['success' => false], Response::HTTP_BAD_REQUEST);
        }

    }


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

    #[Rest\Post("/api/shop/initialize", name: "horeca_api_shop_initialize")]
    #[ParamConverter("body", converter: "fos_rest.request_body")]
    public function initializeShop(Request $request, HorecaInitializeShopBody $body): Response
    {
        $routeName = $request->attributes->get('_route');

        $this->logger->info('[/api/shop/initialize] ' . $request->getContent());

        $this->authorizeRequest($request);

        if (!$body->providerCredentials) {
            $this->logger->warning("[$routeName] ERROR: Missing parameters");
            throw new BadRequestException('Missing parameters:  service_credentials!');
        }
        $credentials = $this->deserializeJson($this->serializeJson($body->providerCredentials), $this->providerApi->getProviderCredentialsClass());

        $ret = $this->providerApi->initializeShop($body->horecaExternalServiceId, $credentials);
        if ($ret) {
            return new JsonResponse(['success' => true]);
        }
        return new JsonResponse(['success' => false], Response::HTTP_BAD_REQUEST);

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
