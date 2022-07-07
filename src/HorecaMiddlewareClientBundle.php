<?php

namespace Horeca\MiddlewareClientBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class HorecaMiddlewareClientBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
