<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\TenantWebhook;
use Psr\Http\Message\ResponseInterface;

interface TenantClientInterface
{
    public function request(string $method, string $uri = '', array $options = []): ResponseInterface;

    public function supportsWebhook(string $name): bool;

    public function getWebhook(string $name): ?TenantWebhook;

    public function sendWebhook(TenantWebhook|string $webhook, array $options = []): ResponseInterface;
}