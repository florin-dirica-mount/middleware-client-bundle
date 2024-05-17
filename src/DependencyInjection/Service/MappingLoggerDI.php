<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Service;

use Horeca\MiddlewareClientBundle\Service\MappingLogger;
use Symfony\Contracts\Service\Attribute\Required;


trait MappingLoggerDI
{
    protected MappingLogger $mappingLogger;

    #[Required]
    public function setMappingLogger(MappingLogger $mappingLogger): void
    {
        $this->mappingLogger = $mappingLogger;
    }
}
