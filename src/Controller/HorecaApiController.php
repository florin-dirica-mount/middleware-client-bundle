<?php

namespace Horeca\MiddlewareClientBundle\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\EntityManagerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\EnvironmentDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\LoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\MessageBusDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\SerializerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\TranslatorDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\ValidatorDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\TenantRepositoryDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProtocolActionsServiceDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProviderApiDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Exception\ApiException;
use Horeca\MiddlewareClientBundle\Message\OrderNotificationMessage;
use Horeca\MiddlewareClientBundle\VO\Horeca\HorecaInitializeShopBody;
use Horeca\MiddlewareClientBundle\VO\Horeca\HorecaRequestDeliveryBody;
use Horeca\MiddlewareClientBundle\VO\Horeca\HorecaSendOrderBody;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HorecaApiController extends AbstractFOSRestController
{
    use LoggerDI;
    use SerializerDI;
    use MessageBusDI;
    use EntityManagerDI;
    use ProviderApiDI;
    use TenantRepositoryDI;
    use TranslatorDI;
    use ValidatorDI;
    use ProtocolActionsServiceDI;
    use EnvironmentDI;

    #[Rest\Post("/api/delivery/request", name: "horeca_api_request_delivery")]
    #[ParamConverter("body", converter: "fos_rest.request_body")]
    public function requestDelivery(Request $request, HorecaRequestDeliveryBody $body): Response
    {
        $this->logger->info(sprintf('[%s] %s', $request->attributes->get('_route'), $request->getContent()));

        $tenant = $this->protocolActionsService->authorizeTenant($request);

        try {
            if (!$body->providerCredentials) {
                $credentialsClass = $this->getParameter('horeca.provider_credentials_class');
                $credentials = $this->tenantRepository->findTenantCredentials($tenant, $credentialsClass);
            } else {
                $serviceCredentials = $this->serializeJson($body->providerCredentials);
                $credentials = $this->deserializeJson($serviceCredentials, $this->providerApi->getProviderCredentialsClass());
            }

            $errors = $this->validator->validate($body->form);

            if (count($errors) > 0) {
                $errorsArray = [];

                foreach ($errors as $violation) {
                    $errorsArray[] = $this->translator->trans($violation->getMessage(), [], 'validators');
                }

                return new Response(json_encode($errorsArray));
            }

            if (!$this->providerApi->requestDelivery($body, $credentials)) {
                return new JsonResponse(['success' => false], Response::HTTP_BAD_REQUEST);
            }

            return new JsonResponse(['success' => true]);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return new JsonResponse(['success' => false], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Rest\Post("/api/order/send", name: "horeca_api_order_send")]
    #[ParamConverter("body", converter: "fos_rest.request_body")]
    public function sendOrder(Request $request, HorecaSendOrderBody $body): Response
    {
        $routeName = $request->attributes->get('_route');
        $this->logger->info(sprintf('[%s] %s', $routeName, $request->getContent()));

        $tenant = $this->protocolActionsService->authorizeTenant($request);

        try {
            $errors = $this->validator->validate($body);
            if ($errors->count() > 0) {
                $messages = [];
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }
                $this->logger->info(sprintf('[%s.%d] Request body errors: ', __METHOD__, __LINE__), $messages);

                throw new ApiException($errors->get(0)->getMessage());
            }

            $order = $this->entityManager->getRepository(OrderNotification::class)->findOneByHorecaOrderId($body->cart->getId());
            $dispatchMessage = false;
            if (!$order) {
                $this->logger->info(sprintf('[%s.%d] New order received: %s', __METHOD__, __LINE__, $body->cart->getId()));

                $order = new OrderNotification();
                $order->setType(OrderNotification::TYPE_NEW_ORDER);

                $dispatchMessage = true;
            }

            $order->setTenant($tenant);
            $order->setHorecaOrderId($body->cart->getId());
            $order->setHorecaPayload($this->serializeJson($body->cart));
            $order->setRestaurantId($body->cart->getRestaurant()->getId());

            if ($body->providerCredentials) {
                $order->setServiceCredentials($this->serializeJson($body->providerCredentials));
            }

            $this->entityManager->persist($order);
            $this->entityManager->flush();

            if ($dispatchMessage) {
                $this->messageBus->dispatch(new OrderNotificationMessage($order));
                $this->logger->info("[$routeName] Dispatched new order: " . $order->getId());
            }

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[%s] %s', __METHOD__, $e->getMessage()));
            $this->logger->error(sprintf('[%s] %s', __METHOD__, $e->getTraceAsString()));

            $data = [
                'success' => false
            ];

            if ($this->isProdEnv()) {
                $data['error'] = $e instanceof ApiException ? $e->getMessage() : 'An error occurred while processing your request. Please try again later.';
            } else {
                $data['error'] = $e->getMessage();
            }
            if ($this->isTestEnv()) {
                $data['trace'] = $e->getTraceAsString();
            }

            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Rest\Post("/api/shop/initialize", name: "horeca_api_shop_initialize")]
    #[ParamConverter("body", converter: "fos_rest.request_body")]
    public function initializeShop(Request $request, HorecaInitializeShopBody $body): Response
    {
        $routeName = $request->attributes->get('_route');
        $this->logger->info(sprintf('[%s] %s', $routeName, $request->getContent()));

        $tenant = $this->protocolActionsService->authorizeTenant($request);

        try {
            if (!$body->providerCredentials) {
                $credentialsClass = $this->getParameter('horeca.provider_credentials_class');
                $credentials = $this->tenantRepository->findTenantCredentials($tenant, $credentialsClass);
            } else {
                $credentials = $this->deserializeJson($this->serializeJson($body->providerCredentials), $this->providerApi->getProviderCredentialsClass());
            }

            if (!$this->providerApi->initializeShop($body->horecaExternalServiceId, $credentials)) {
                return new JsonResponse(['success' => false], Response::HTTP_BAD_REQUEST);
            }

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[%s] %s', __METHOD__, $e->getMessage()));

            return new JsonResponse(['success' => false], Response::HTTP_BAD_REQUEST);
        }
    }
}
