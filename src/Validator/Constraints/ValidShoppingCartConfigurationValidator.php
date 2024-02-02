<?php

namespace Horeca\MiddlewareClientBundle\Validator\Constraints;

use Horeca\MiddlewareCommonLib\Constants\DeliveryType;
use Horeca\MiddlewareCommonLib\Constants\InvoiceType;
use Horeca\MiddlewareCommonLib\Constants\PaymentStatus;
use Horeca\MiddlewareCommonLib\Constants\PaymentType;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidShoppingCartConfigurationValidator extends ConstraintValidator
{

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidShoppingCartConfiguration) {
            throw new UnexpectedTypeException($constraint, ValidShoppingCartConfiguration::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof ShoppingCart) {
            $this->context->buildViolation('The value must be an instance of "{{ class }}"')
                ->setParameter('{{ class }}', ShoppingCart::class)
                ->setCode(ValidShoppingCartConfiguration::INVALID_SHOPPING_CART)
                ->addViolation();
        }

        if ($value->getInvoiceType() === InvoiceType::COMPANY && !$value->getCustomerCompany()) {
            $this->context->buildViolation('The ShoppingCart "{{ cart }}" has missing company information.')
                ->setParameter('{{ cart }}', $this->formatValue($value, ConstraintValidator::OBJECT_TO_STRING))
                ->setCode(ValidShoppingCartConfiguration::MISSING_COMPANY_INFORMATION)
                ->addViolation();
        }

        if ($value->getDeliveryMethod() === DeliveryType::DELIVERY && !$value->getDeliveryAddress()) {
            $this->context->buildViolation('The ShoppingCart "{{ cart }}" has missing delivery address information.')
                ->setParameter('{{ cart }}', $this->formatValue($value, ConstraintValidator::OBJECT_TO_STRING))
                ->setCode(ValidShoppingCartConfiguration::MISSING_DELIVERY_ADDRESS)
                ->addViolation();
        }

        if ($value->getPaymentMethod() === PaymentType::CREDIT_CARD_ONLINE && $value->getPaymentStatus() !== PaymentStatus::SUCCESS) {
            $this->context->buildViolation('The ShoppingCart "{{ cart }}" has not confirmed payment status for credit card online payment.')
                ->setParameter('{{ cart }}', $this->formatValue($value, ConstraintValidator::OBJECT_TO_STRING))
                ->setCode(ValidShoppingCartConfiguration::PAYMENT_NOT_CONFIRMED)
                ->addViolation();
        }
    }
}