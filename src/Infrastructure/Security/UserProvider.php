<?php

namespace App\Infrastructure\Security;

use App\Domain\Users\UsersRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    private UsersRepository $usersRepository;
    
    public function __construct(UsersRepository $usersRepository)
    {
        $this->usersRepository = $usersRepository;
    }
    
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->usersRepository->findByEmail($identifier);
        
        if (!$user) {
            throw new \Symfony\Component\Security\Core\Exception\UserNotFoundException(
                sprintf('User with email "%s" not found.', $identifier)
            );
        }
        
        return $user;
    }
    
    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }
    
    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }
    
    public function supportsClass(string $class): bool
    {
        return $class === 'App\Domain\Users\User';
    }
}