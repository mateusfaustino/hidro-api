<?php

declare(strict_types=1);

namespace App\Application\DTO\User;

/**
 * DTO para critérios de listagem de usuários
 * Suporta paginação, ordenação, filtros e busca
 */
final readonly class ListUsersDTO
{
    public function __construct(
        public string $schoolId,
        public int $page = 1,
        public int $perPage = 20,
        public ?string $sortBy = 'created_at',
        public string $sortOrder = 'DESC',
        public ?string $role = null,
        public ?string $status = null,
        public ?string $search = null
    ) {
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    public function getLimit(): int
    {
        return $this->perPage;
    }
}
