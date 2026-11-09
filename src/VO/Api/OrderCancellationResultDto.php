<?php

namespace Horeca\MiddlewareClientBundle\VO\Api;

use Horeca\MiddlewareCommonLib\Constants\OrderCancellationResult;

/**
 * The middleware's answer to POST /api/order/cancel and POST /api/order/cancel/check: `result` is one of
 * OrderCancellationResult, `message` says why, in English, for Great Food's logs only (never shown to a customer).
 */
final class OrderCancellationResultDto
{
    public string $result;

    public ?string $message;

    private function __construct(string $result, ?string $message)
    {
        $this->result = $result;
        $this->message = $message;
    }

    /** Check only: the order can be cancelled now. */
    public static function cancellable(): self
    {
        return new self(OrderCancellationResult::CANCELLABLE, null);
    }

    public static function cancelled(?string $message = null): self
    {
        return new self(OrderCancellationResult::CANCELLED, $message);
    }

    public static function notCancellable(string $message): self
    {
        return new self(OrderCancellationResult::NOT_CANCELLABLE, $message);
    }

    public static function notFound(string $message): self
    {
        return new self(OrderCancellationResult::NOT_FOUND, $message);
    }

    public static function unsupported(): self
    {
        return new self(OrderCancellationResult::UNSUPPORTED, 'This integration cannot cancel orders');
    }
}
