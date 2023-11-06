<?php

namespace Horeca\MiddlewareClientBundle\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\EntityManagerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\LoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\MessageBusDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\SerializerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\TranslatorDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\ValidatorDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\TenantRepositoryDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProviderApiDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareClientBundle\Message\OrderNotificationMessage;
use Horeca\MiddlewareClientBundle\VO\Horeca\HorecaInitializeShopBody;
use Horeca\MiddlewareClientBundle\VO\Horeca\HorecaRequestDeliveryBody;
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
    use ProviderApiDI;
    use TenantRepositoryDI;
    use TranslatorDI;
    use ValidatorDI;

    #[Rest\Post("/api/delivery/request", name: "horeca_api_request_delivery")]
    #[ParamConverter("body", converter: "fos_rest.request_body")]
    public function requestDelivery(Request $request, HorecaRequestDeliveryBody $body): Response
    {
        $this->logger->info(sprintf('[%s] %s', $request->attributes->get('_route'), $request->getContent()));

        $tenant = $this->authorizeTenant($request);

        try {
            if (!$body->providerCredentials && $tenant) {
                $credentialsClass = $this->getParameter('horeca.provider_credentials_class');
                $credentials = $this->tenantRepository->findTenantCredentials($tenant, $credentialsClass);
            } elseif ($body->providerCredentials) {
                $serviceCredentials = $this->serializeJson($body->providerCredentials);
                $credentials = $this->deserializeJson($serviceCredentials, $this->providerApi->getProviderCredentialsClass());
            } else {
                throw new BadRequestException('Missing parameters: service_credentials!');
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

        $tenant = $this->authorizeTenant($request);

        try {
            if (!$body->cart) {
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
                $order->setType(OrderNotification::TYPE_NEW_ORDER);
                $order->setHorecaOrderId($body->cart->getId());
                $order->setHorecaPayload($this->serializeJson($body->cart));
                $order->setRestaurantId($body->cart->getRestaurant()->getId());
                $order->setTenant($tenant);

                if ($body->providerCredentials) {
                    $order->setServiceCredentials($this->serializeJson($body->providerCredentials));
                }

                $this->entityManager->persist($order);
                $this->entityManager->flush();

                $this->logger->info("[$routeName] Created new order: " . $order->getId());
                $this->messageBus->dispatch(new OrderNotificationMessage($order));
                $this->logger->info("[$routeName] Dispatched new order: " . $order->getId());
            }

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[%s] %s', __METHOD__, $e->getMessage()));

            return new JsonResponse(['success' => false], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Rest\Post("/api/shop/initialize", name: "horeca_api_shop_initialize")]
    #[ParamConverter("body", converter: "fos_rest.request_body")]
    public function initializeShop(Request $request, HorecaInitializeShopBody $body): Response
    {
        $routeName = $request->attributes->get('_route');
        $this->logger->info(sprintf('[%s] %s', $routeName, $request->getContent()));

        $tenant = $this->authorizeTenant($request);

        try {
            if (!$body->providerCredentials && $tenant) {
                $credentialsClass = $this->getParameter('horeca.provider_credentials_class');
                $credentials = $this->tenantRepository->findTenantCredentials($tenant, $credentialsClass);
            } elseif ($body->providerCredentials) {
                $credentials = $this->deserializeJson($this->serializeJson($body->providerCredentials), $this->providerApi->getProviderCredentialsClass());
            } else {
                $this->logger->warning("[$routeName] ERROR: Missing parameters");
                throw new BadRequestException('Missing parameters:  service_credentials!');
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

    /**
     * @throws AccessDeniedException
     */
    protected function authorizeTenant(Request $request): ?Tenant
    {
        $routeName = $request->attributes->get('_route');

        $auth = $request->headers->get('Authorization');
        if (!$auth) {
            $auth = $request->headers->get('Api-Key');
        }

        if (!$auth) {
            $this->logger->warning("[$routeName] ERROR: Missing authorization/api key");

            throw new AccessDeniedException();
        }

        switch ($auth) {
            case str_starts_with($auth, 'Basic '):
                $base64Credentials = substr($auth, 6);

                $credentials = base64_decode($base64Credentials);

                list($id, $api_key) = explode(':', $credentials);
                $tenant = $this->tenantRepository->findOneByApiKeyAndId((string)$api_key, (string)$id);
                if (!$tenant) {
                    $this->logger->warning("[$routeName] ERROR: Invalid id/api key");

                    throw new AccessDeniedException();
                }
                break;
            default:
                $tenant = $this->tenantRepository->findOneByApiKey((string)$auth);
                if (!$tenant) {
                    $this->logger->warning("[$routeName] ERROR: Invalid api key");

                    throw new AccessDeniedException();
                }
        }

        return $tenant;
    }
}
