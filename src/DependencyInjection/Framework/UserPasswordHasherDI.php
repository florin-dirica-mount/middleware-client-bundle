<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Framework;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

trait UserPasswordHasherDI
{
    protected UserPasswordHasherInterface $userPasswordHasher;

    /**
     * @required
     */
    public function setUserPasswordHasherInterface(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }
}
