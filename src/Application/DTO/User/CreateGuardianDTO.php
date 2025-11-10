<?php

declare(strict_types=1);

namespace App\Application\DTO\User;

/**
 * DTO para criação de usuário Responsável
 */
final class CreateGuardianDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $phone,
        public readonly string $name,
        public readonly string $password,
        public readonly string $schoolId
    ) {
    }
}
