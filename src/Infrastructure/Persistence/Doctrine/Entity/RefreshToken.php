<?php

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use App\Infrastructure\Persistence\Doctrine\Repository\RefreshTokenRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
#[ORM\Table(name: 'refresh_tokens')]
#[ORM\Index(name: 'idx_refresh_token_token', columns: ['refresh_token'])]
#[ORM\Index(name: 'idx_refresh_token_username', columns: ['username'])]
class RefreshToken
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36)]
    private string $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $username;

    #[ORM\Column(type: Types::STRING, length: 128, unique: true)]
    private string $refreshToken;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $validUntil;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::STRING, length: 45, nullable: true)]
    private ?string $createdByIp = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $revokedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct(string $username, string $hashedRefreshToken, DateTimeImmutable $validUntil)
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->username = $username;
        $this->refreshToken = $hashedRefreshToken;
        $this->validUntil = $validUntil;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getValidUntil(): DateTimeImmutable
    {
        return $this->validUntil;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCreatedByIp(): ?string
    {
        return $this->createdByIp;
    }

    public function setCreatedByIp(?string $createdByIp): void
    {
        $this->createdByIp = $createdByIp;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }

    public function markRevoked(): void
    {
        $this->revokedAt = new DateTimeImmutable();
        $this->updatedAt = $this->revokedAt;
    }

    public function isRevoked(): bool
    {
        return $this->revokedAt !== null;
    }

    public function isExpired(DateTimeImmutable $now = new DateTimeImmutable()): bool
    {
        return $now > $this->validUntil;
    }

    public function rotate(string $hashedToken, DateTimeImmutable $validUntil, ?string $ip = null, ?string $userAgent = null): void
    {
        $this->refreshToken = $hashedToken;
        $this->validUntil = $validUntil;
        $this->updatedAt = new DateTimeImmutable();
        $this->createdByIp = $ip;
        $this->userAgent = $userAgent;
        $this->revokedAt = null;
    }
}
