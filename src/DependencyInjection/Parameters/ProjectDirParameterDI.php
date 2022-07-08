<?php


namespace Horeca\MiddlewareClientBundle\DependencyInjection\Parameters;


use Symfony\Component\DependencyInjection\ContainerInterface;

trait ProjectDirParameterDI
{
    protected string $projectDir;

    /**
     * @required
     */
    public function setProjectDirParameter(ContainerInterface $container)
    {
        $this->projectDir = $container->getParameter('kernel.project_dir');
    }
}
