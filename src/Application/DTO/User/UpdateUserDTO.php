<?php

declare(strict_types=1);

namespace App\Application\DTO\User;

/**
 * DTO para atualização de usuário
 */
final class UpdateUserDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly ?string $name = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null
    ) {
    }
}
