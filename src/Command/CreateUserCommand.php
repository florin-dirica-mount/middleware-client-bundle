<?php

namespace Horeca\MiddlewareClientBundle\Command;

use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\EntityManagerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\UserPasswordHasherDI;
use Horeca\MiddlewareClientBundle\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateUserCommand extends Command
{
    protected static $defaultName = 'horeca:user:create';

    use EntityManagerDI;
    use UserPasswordHasherDI;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $email = $helper->ask($input, $output, new Question('User email: '));

        $question = new Question('User password: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $plainPassword = $helper->ask($input, $output, $question);

        $role = $helper->ask($input, $output, new Question('User role (ROLE_USER, ROLE_ADMIN): '));

        $user = new User();
        $user->setEmail($email);
        $user->setUsername(substr($email, 0, strpos($email, '@') - 1));
        $user->setRoles([User::ROLE_USER, $role]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $plainPassword));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return 0;
    }
}
