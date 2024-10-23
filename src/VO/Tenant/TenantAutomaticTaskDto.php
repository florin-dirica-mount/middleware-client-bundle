<?php

namespace Horeca\MiddlewareClientBundle\VO\Tenant;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class TenantAutomaticTaskDto
{

    public \DateTime $hour;

    public array $payload = [];

}
