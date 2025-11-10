<?php

declare(strict_types=1);

namespace App\Application\UseCase\User;

use App\Application\DTO\User\UserResponseDTO;
use App\Domain\Users\UsersRepository;

/**
 * Use Case: Listar Usuários por Escola
 * 
 * Multi-tenant: Lista apenas usuários da escola especificada
 */
final class ListUsersBySchoolUseCase
{
    public function __construct(
        private readonly UsersRepository $usersRepository
    ) {
    }

    /**
     * @return UserResponseDTO[]
     */
    public function execute(string $schoolId): array
    {
        $users = $this->usersRepository->findBySchoolId($schoolId);

        return array_map(
            fn($user) => UserResponseDTO::fromEntity($user),
            $users
        );
    }
}
