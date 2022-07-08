<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Framework;

use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\RequestStack;

trait HeaderBagDI
{
    protected HeaderBag $headerBag;

    /**
     * @required
     */
    public function setHeaderBag(RequestStack $requestStack)
    {
        if ($requestStack->getCurrentRequest()) {
            $this->headerBag = new HeaderBag($requestStack->getCurrentRequest()->headers->all());
        } else {
            $this->headerBag = new HeaderBag();
        }
    }
}
