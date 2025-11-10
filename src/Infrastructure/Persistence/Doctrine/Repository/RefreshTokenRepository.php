<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Infrastructure\Persistence\Doctrine\Entity\RefreshToken;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;

/**
 * Repositório de Refresh Tokens usando DBAL
 * Contorna problemas de mapeamento ORM
 */
class RefreshTokenRepository
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function save(RefreshToken $refreshToken): void
    {
        $data = [
            'id' => $refreshToken->getId(),
            'refresh_token' => $refreshToken->getRefreshToken(),
            'username' => $refreshToken->getUsername(),
            'valid_until' => $refreshToken->getValidUntil()->format('Y-m-d H:i:s'),
            'created_by_ip' => $refreshToken->getCreatedByIp(),
            'user_agent' => $refreshToken->getUserAgent(),
            'created_at' => $refreshToken->getCreatedAt()->format('Y-m-d H:i:s'),
            'revoked_at' => $refreshToken->isRevoked() ? (new DateTimeImmutable())->format('Y-m-d H:i:s') : null,
            'updated_at' => null,
        ];
        
        // Verifica se já existe
        $exists = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM refresh_tokens WHERE id = ?',
            [$refreshToken->getId()]
        );
        
        if ($exists) {
            $this->connection->update('refresh_tokens', $data, ['id' => $refreshToken->getId()]);
        } else {
            $this->connection->insert('refresh_tokens', $data);
        }
    }

    public function delete(RefreshToken $refreshToken): void
    {
        $this->connection->delete('refresh_tokens', ['id' => $refreshToken->getId()]);
    }

    public function findValidToken(string $hashedToken, DateTimeImmutable $now): ?RefreshToken
    {
        $data = $this->connection->fetchAssociative(
            'SELECT * FROM refresh_tokens 
             WHERE refresh_token = ? 
             AND valid_until > ? 
             AND revoked_at IS NULL',
            [$hashedToken, $now->format('Y-m-d H:i:s')]
        );
        
        return $data ? $this->hydrateRefreshToken($data) : null;
    }

    public function revokeByHashedToken(string $hashedToken): void
    {
        $this->connection->update(
            'refresh_tokens',
            ['revoked_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s')],
            ['refresh_token' => $hashedToken]
        );
    }
    
    private function hydrateRefreshToken(array $data): RefreshToken
    {
        // Usa reflection para criar o RefreshToken
        $reflection = new \ReflectionClass(RefreshToken::class);
        $refreshToken = $reflection->newInstanceWithoutConstructor();
        
        // Define propriedades usando reflection
        $this->setProperty($refreshToken, 'id', $data['id']);
        $this->setProperty($refreshToken, 'refreshToken', $data['refresh_token']);
        $this->setProperty($refreshToken, 'username', $data['username']);
        $this->setProperty($refreshToken, 'validUntil', new DateTimeImmutable($data['valid_until']));
        $this->setProperty($refreshToken, 'createdByIp', $data['created_by_ip']);
        $this->setProperty($refreshToken, 'userAgent', $data['user_agent']);
        $this->setProperty($refreshToken, 'createdAt', new DateTimeImmutable($data['created_at']));
        $this->setProperty($refreshToken, 'revokedAt', $data['revoked_at'] ? new DateTimeImmutable($data['revoked_at']) : null);
        $this->setProperty($refreshToken, 'updatedAt', $data['updated_at'] ? new DateTimeImmutable($data['updated_at']) : null);
        
        return $refreshToken;
    }
    
    private function setProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new \ReflectionProperty($object, $property);
        $reflection->setAccessible(true);
        $reflection->setValue($object, $value);
    }
}
