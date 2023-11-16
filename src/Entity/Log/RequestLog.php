<?php

namespace Horeca\MiddlewareClientBundle\Entity\Log;

use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\Entity\AbstractEntity;

#[ORM\MappedSuperclass]
abstract class RequestLog extends AbstractEntity
{

    #[ORM\Column(name: "method", type: "string", length: 10, nullable: false)]
    protected string $method;

    #[ORM\Column(name: "uri", type: "string", nullable: false)]
    protected string $uri;

    #[ORM\Column(name: "status_code", type: "integer", nullable: false)]
    protected int $statusCode;

    #[ORM\Column(name: "headers", type: "json", nullable: true)]
    protected ?string $headers = null;

    #[ORM\Column(name: "query_params", type: "json", nullable: true)]
    protected ?string $query = null;

    #[ORM\Column(name: "body_params", type: "json", nullable: true)]
    protected ?string $body = null;

    #[ORM\Column(name: "response_body", type: "json", nullable: true)]
    protected ?string $response = null;

    #[ORM\Column(name: "exception", type: "json", nullable: true)]
    protected ?string $exception = null;

    #[ORM\Column(name: "request_duration", type: "float", nullable: true)]
    protected ?float $duration = null;

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function getHeaders(): ?string
    {
        return $this->headers;
    }

    public function setHeaders(?string $headers): void
    {
        $this->headers = $headers;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setQuery(?string $query): void
    {
        $this->query = $query;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): void
    {
        $this->body = $body;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse(?string $response): void
    {
        $this->response = $response;
    }

    public function getException(): ?string
    {
        return $this->exception;
    }

    public function setException(?string $exception): void
    {
        $this->exception = $exception;
    }

    public function getDuration(): ?float
    {
        return $this->duration;
    }

    public function setDuration(?float $duration): void
    {
        $this->duration = $duration;
    }

}
