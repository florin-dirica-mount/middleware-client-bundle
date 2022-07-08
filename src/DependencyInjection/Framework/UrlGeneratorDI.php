<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Framework;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

trait UrlGeneratorDI
{
    protected UrlGeneratorInterface $urlGenerator;

    /**
     * @required
     */
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }
}
