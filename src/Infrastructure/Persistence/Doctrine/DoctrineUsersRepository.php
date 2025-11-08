<?php

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Users\User;
use App\Domain\Users\UsersRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineUsersRepository extends ServiceEntityRepository implements UsersRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
    
    public function findById(string $id): ?User
    {
        return $this->find($id);
    }
    
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }
    
    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
    
    public function delete(User $user): void
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }
}