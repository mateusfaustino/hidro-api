<?php

namespace App\Entity;

use App\Domain\Common\AggregateRoot;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User implements AggregateRoot, UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private string $id;
    
    #[ORM\Column(type: 'string', unique: true)]
    private string $email;
    
    #[ORM\Column(type: 'string')]
    private string $password;
    
    #[ORM\Column(type: 'string')]
    private string $name;
    
    #[ORM\Column(type: 'json')]
    private array $roles = [];
    
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $schoolId = null;
    
    public function __construct(string $id, string $email, string $name, array $roles = ['ROLE_USER'])
    {
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
        $this->roles = $roles;
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getEmail(): string
    {
        return $this->email;
    }
    
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
    
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        
        return array_unique($roles);
    }
    
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }
    
    public function getPassword(): string
    {
        return $this->password;
    }
    
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }
    
    public function getSchoolId(): ?string
    {
        return $this->schoolId;
    }
    
    public function setSchoolId(?string $schoolId): self
    {
        $this->schoolId = $schoolId;
        return $this;
    }
    
    // UserInterface methods
    public function getUserIdentifier(): string
    {
        return $this->email;
    }
    
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }
}