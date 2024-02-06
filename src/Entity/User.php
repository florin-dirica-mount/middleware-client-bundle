<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\Repository\UserRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "hmc_users")]
#[UniqueEntity(fields: ["email"], message: "There is already an account with this email")]
class User extends DefaultEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_USER = 'ROLE_USER';

    #[ORM\Column(name: "email", type: "string", length: 180, unique: true)]
    private string $email;

    #[ORM\Column(name: "username", type: "string", length: 180, unique: true)]
    private string $username;

    #[ORM\Column(name: "roles", type: "array", nullable: true)]
    private array $roles = [User::ROLE_USER];

    #[ORM\Column(name: "password", type: "string", nullable: false)]
    private ?string $password = null;

    #[ORM\Column(name: "salt", type: "string", nullable: false)]
    private string $salt;

    public function __construct()
    {
        parent::__construct();
        try {
            $this->salt = bin2hex(random_bytes(36));
        } catch (\Exception $e) {
            $this->salt = Uuid::uuid4()->toString();
        }
    }

    public function toString(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return array|string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array|string[] $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @inheritDoc
     */
    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
        $this->email = "anon_" . \time();
        $this->username = "anon_" . \time();
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * @param string $salt
     */
    public function setSalt(string $salt): void
    {
        $this->salt = $salt;
    }
}
