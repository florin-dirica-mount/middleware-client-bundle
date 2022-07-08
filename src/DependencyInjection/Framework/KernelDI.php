<?php


namespace Horeca\MiddlewareClientBundle\DependencyInjection\Framework;


use Symfony\Component\HttpKernel\KernelInterface;

trait KernelDI
{
    protected KernelInterface $kernel;

    /**
     * @required
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }
}
