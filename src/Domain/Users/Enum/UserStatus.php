<?php

declare(strict_types=1);

namespace App\Domain\Users\Enum;

/**
 * Enum para Status de Usuário
 */
enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case PENDING = 'pending';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Ativo',
            self::INACTIVE => 'Inativo',
            self::SUSPENDED => 'Suspenso',
            self::PENDING => 'Pendente',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canLogin(): bool
    {
        return $this === self::ACTIVE;
    }
}
