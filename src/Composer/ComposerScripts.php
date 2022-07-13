<?php

namespace Horeca\MiddlewareClientBundle\Composer;

use Composer\Script\Event;

final class ComposerScripts
{
    public static function postInstall(Event $event)
    {
        die('wtf');
    }

    public static function postUpdate(Event $event)
    {
        die('wtf');
    }
}
