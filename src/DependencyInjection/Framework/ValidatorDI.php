<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Framework;

use Symfony\Component\Validator\Validator\ValidatorInterface;

trait ValidatorDI
{
    protected ValidatorInterface $validator;

    /**
     * @required
     */
    public function setValidator(ValidatorInterface $validator): void
    {
        $this->validator = $validator;
    }

    public function getValidationErrorMessage($object): ?string
    {
        $errors = $this->validator->validate($object);

        return $errors->count() > 0 ? (string) $errors->get(0)->getMessage() : null;
    }
}
