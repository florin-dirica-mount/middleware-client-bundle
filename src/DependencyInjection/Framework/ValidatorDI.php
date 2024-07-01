<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Framework;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait ValidatorDI
{

    private ValidatorInterface $validator;


    #[Required]
    public function setValidator(ValidatorInterface $validator): void
    {
        $this->validator = $validator;
    }

}
