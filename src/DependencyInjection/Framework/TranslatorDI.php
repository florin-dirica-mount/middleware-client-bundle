<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Framework;

use Symfony\Contracts\Translation\TranslatorInterface;

trait TranslatorDI
{
    protected TranslatorInterface $translator;

    /**
     * @required
     */
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }
}