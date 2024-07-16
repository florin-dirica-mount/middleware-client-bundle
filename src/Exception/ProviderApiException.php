<?php

namespace Horeca\MiddlewareClientBundle\Exception;

class ProviderApiException extends ApiException
{

    private string $responseContent;

    public function __construct(string $message, string $responseContent, ?int $code = null)
    {
        parent::__construct($message, $code);

        $this->responseContent = $responseContent;
    }

    public function getResponseContent(): string
    {
        return $this->responseContent;
    }

}