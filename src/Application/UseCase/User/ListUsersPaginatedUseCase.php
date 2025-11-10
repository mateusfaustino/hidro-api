<?php

declare(strict_types=1);

namespace App\Application\UseCase\User;

use App\Application\DTO\User\ListUsersDTO;
use App\Application\DTO\User\PaginatedUsersResponseDTO;
use App\Application\DTO\User\UserResponseDTO;
use App\Domain\Users\UsersRepository;

/**
 * Use Case: Listar Usuários Paginado
 * 
 * Suporta paginação, ordenação, filtros e busca
 * Multi-tenant: Lista apenas usuários da escola especificada
 */
final class ListUsersPaginatedUseCase
{
    public function __construct(
        private readonly UsersRepository $usersRepository
    ) {
    }

    public function execute(ListUsersDTO $dto): PaginatedUsersResponseDTO
    {
        $result = $this->usersRepository->findBySchoolIdPaginated(
            schoolId: $dto->schoolId,
            limit: $dto->getLimit(),
            offset: $dto->getOffset(),
            sortBy: $dto->sortBy,
            sortOrder: $dto->sortOrder,
            role: $dto->role,
            status: $dto->status,
            search: $dto->search
        );

        $userDTOs = array_map(
            fn($user) => UserResponseDTO::fromEntity($user),
            $result['users']
        );

        $totalPages = (int) ceil($result['total'] / $dto->perPage);

        $filters = [];
        if ($dto->role) $filters['role'] = $dto->role;
        if ($dto->status) $filters['status'] = $dto->status;
        if ($dto->search) $filters['search'] = $dto->search;

        return new PaginatedUsersResponseDTO(
            data: $userDTOs,
            total: $result['total'],
            page: $dto->page,
            perPage: $dto->perPage,
            totalPages: $totalPages,
            sortBy: $dto->sortBy,
            sortOrder: $dto->sortOrder,
            filters: $filters
        );
    }
}
