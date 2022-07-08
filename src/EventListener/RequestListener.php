<?php

namespace Horeca\MiddlewareClientBundle\EventListener;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\EnvironmentDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\LoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\RequestLogRepositoryDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\RequestServiceDI;
use Horeca\MiddlewareClientBundle\Exception\ApiException;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class RequestListener
{
    use LoggerDI;
    use EnvironmentDI;
    use RequestServiceDI;
    use RequestLogRepositoryDI;

    private bool $requestExceptionLoggingEnabled = true;

    private ?\Throwable $exception = null;

    public function enableRequestExceptionLogging(bool $value): void
    {
        $this->requestExceptionLoggingEnabled = $value;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            // don't do anything if it's not the main request
            return;
        }

        $this->requestService->init($event->getRequest());

        $route = $event->getRequest()->get('_route');
        $headers = new HeaderBag($event->getRequest()->headers->all());

        $this->logger->info(sprintf('[%s] %s headers: %s, query: %s, body: %s',
            $route,
            $event->getRequest()->getMethod(),
            json_encode($headers->all()),
            json_encode($event->getRequest()->query->all()),
            json_encode($event->getRequest()->request->all())
        ));
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            // don't do anything if it's not the main request
            return;
        }

        $message = sprintf('[%s] %d %s',
            $event->getRequest()->get('_route'),
            $event->getResponse()->getStatusCode(),
            $event->getResponse()->getContent()
        );

        $statusCode = $event->getResponse()->getStatusCode();
        if ($statusCode === Response::HTTP_INTERNAL_SERVER_ERROR) {
            $this->logger->critical($message);
        } elseif ($statusCode >= Response::HTTP_BAD_REQUEST) {
            $this->logger->error($message);
        } else {
            $this->logger->info($message);
        }

        if ($this->requestService->isLoggableStatusCode($statusCode)) {
            $this->requestService->createRequestLog($event->getRequest(), $event->getResponse(), $this->exception);
        }
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $route = $event->getRequest()->get('_route');
        $exception = $event->getThrowable();
        $this->exception = $event->getThrowable();

        if ($exception instanceof ApiException || $exception instanceof UniqueConstraintViolationException) {
            $this->logger->error(sprintf('[%s] %s', $route, $exception->getMessage()));
            $this->logger->error(sprintf('[%s] %s', $route, $exception->getTraceAsString()));
        } else {
            $this->logger->critical(sprintf('[%s] %s', $route, $exception->getMessage()));
            $this->logger->critical(sprintf('[%s] %s', $route, $exception->getTraceAsString()));
        }
    }

}
