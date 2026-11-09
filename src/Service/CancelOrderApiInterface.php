<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareClientBundle\VO\Api\OrderCancellationResultDto;
use Horeca\MiddlewareClientBundle\VO\Horeca\HorecaCancelOrderBody;
use Horeca\MiddlewareClientBundle\VO\Horeca\HorecaCheckOrderCancellationBody;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderCredentialsInterface;

/**
 * Implemented by a provider API that can cancel an order Great Food sent to it: POST /api/order/cancel/check (can it be
 * cancelled now?) and POST /api/order/cancel. The implementation applies its provider's rules; a provider API without
 * it answers "unsupported" to both.
 */
interface CancelOrderApiInterface
{
    /**
     * Read only, before Great Food shows the cancellation form: cancellable / not_cancellable / not_found. Throw only
     * when the provider could not be asked.
     *
     * @param ProviderCredentialsInterface $credentials
     */
    public function canCancelOrder(Tenant $tenant, HorecaCheckOrderCancellationBody $body, $credentials): OrderCancellationResultDto;

    /**
     * A refusal by the provider's rules is an answer (not_cancellable / not_found), not an exception; throw only when the
     * provider could not be asked (it is down, the credentials are wrong, ...).
     *
     * @param ProviderCredentialsInterface $credentials
     */
    public function cancelOrder(Tenant $tenant, HorecaCancelOrderBody $body, $credentials): OrderCancellationResultDto;
}
