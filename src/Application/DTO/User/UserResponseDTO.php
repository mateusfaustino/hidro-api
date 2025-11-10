<?php

declare(strict_types=1);

namespace App\Application\DTO\User;

use App\Domain\Users\User;
use DateTimeImmutable;

/**
 * DTO para resposta de usuário
 */
final class UserResponseDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $email,
        public readonly string $name,
        public readonly ?string $phone,
        public readonly array $roles,
        public readonly string $status,
        public readonly ?string $schoolId,
        public readonly DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
        public readonly ?DateTimeImmutable $lastLoginAt
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId()->value(),
            email: $user->getEmail()->value(),
            name: $user->getName(),
            phone: $user->getPhone()?->value(),
            roles: $user->getRoles(),
            status: $user->getStatus()->value,
            schoolId: $user->getSchoolId(),
            createdAt: $user->getCreatedAt(),
            updatedAt: $user->getUpdatedAt(),
            lastLoginAt: $user->getLastLoginAt()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'phone' => $this->phone,
            'roles' => $this->roles,
            'status' => $this->status,
            'school_id' => $this->schoolId,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'last_login_at' => $this->lastLoginAt?->format('Y-m-d H:i:s'),
        ];
    }
}
