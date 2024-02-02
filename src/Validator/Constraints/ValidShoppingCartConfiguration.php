<?php

namespace Horeca\MiddlewareClientBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ValidShoppingCartConfiguration extends Constraint
{
    const INVALID_SHOPPING_CART = 601;
    const MISSING_COMPANY_INFORMATION = 602;
    const MISSING_DELIVERY_ADDRESS = 603;
    const PAYMENT_NOT_CONFIRMED = 604;

    public string $message = 'The ShoppingCart *{{ cart }}* has invalid configuration.';
}