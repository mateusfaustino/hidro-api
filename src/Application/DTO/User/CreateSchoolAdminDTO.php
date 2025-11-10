<?php

declare(strict_types=1);

namespace App\Application\DTO\User;

/**
 * DTO para criação de Administrador da Escola
 */
final class CreateSchoolAdminDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $name,
        public readonly string $password,
        public readonly string $schoolId,
        public readonly ?string $phone = null
    ) {
    }
}
