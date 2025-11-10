<?php

declare(strict_types=1);

namespace App\Application\DTO\User;

/**
 * DTO para resposta paginada de usuários
 */
final readonly class PaginatedUsersResponseDTO
{
    /**
     * @param UserResponseDTO[] $data
     */
    public function __construct(
        public array $data,
        public int $total,
        public int $page,
        public int $perPage,
        public int $totalPages,
        public ?string $sortBy = null,
        public ?string $sortOrder = null,
        public array $filters = []
    ) {
    }

    public function toArray(): array
    {
        return [
            'data' => array_map(fn($user) => $user->toArray(), $this->data),
            'meta' => [
                'total' => $this->total,
                'page' => $this->page,
                'per_page' => $this->perPage,
                'total_pages' => $this->totalPages,
                'sort_by' => $this->sortBy,
                'sort_order' => $this->sortOrder,
                'filters' => $this->filters,
            ],
        ];
    }
}
