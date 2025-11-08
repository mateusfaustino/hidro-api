<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Infrastructure\Persistence\Doctrine\Entity\RefreshToken;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RefreshToken>
 */
class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function save(RefreshToken $refreshToken): void
    {
        $this->_em->persist($refreshToken);
        $this->_em->flush();
    }

    public function delete(RefreshToken $refreshToken): void
    {
        $this->_em->remove($refreshToken);
        $this->_em->flush();
    }

    public function findValidToken(string $hashedToken, DateTimeImmutable $now): ?RefreshToken
    {
        return $this->createQueryBuilder('rt')
            ->where('rt.refreshToken = :token')
            ->andWhere('rt.validUntil > :now')
            ->andWhere('rt.revokedAt IS NULL')
            ->setParameter('token', $hashedToken)
            ->setParameter('now', $now)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function revokeByHashedToken(string $hashedToken): void
    {
        $refreshToken = $this->findOneBy(['refreshToken' => $hashedToken]);

        if ($refreshToken === null) {
            return;
        }

        $refreshToken->markRevoked();
        $this->_em->flush();
    }
}
