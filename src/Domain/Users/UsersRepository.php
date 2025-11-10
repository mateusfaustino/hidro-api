<?php

declare(strict_types=1);

namespace App\Domain\Users;

use App\Domain\Users\ValueObject\Email;
use App\Domain\Users\ValueObject\UserId;

/**
 * Interface do Repositório de Usuários
 * 
 * Define o contrato para persistência de usuários
 * Seguindo princípios de DDD - Repository Pattern
 */
interface UsersRepository
{
    /**
     * Busca usuário por ID
     */
    public function findById(UserId $id): ?User;
    
    /**
     * Busca usuário por Email
     */
    public function findByEmail(Email $email): ?User;
    
    /**
     * Busca usuários por escola (multi-tenant)
     * 
     * @return User[]
     */
    public function findBySchoolId(string $schoolId): array;
    
    /**
     * Busca usuários paginados com filtros
     * 
     * @return array{users: User[], total: int}
     */
    public function findBySchoolIdPaginated(
        string $schoolId,
        int $limit,
        int $offset,
        ?string $sortBy = 'created_at',
        string $sortOrder = 'DESC',
        ?string $role = null,
        ?string $status = null,
        ?string $search = null
    ): array;
    
    /**
     * Busca usuários por role
     * 
     * @return User[]
     */
    public function findByRole(string $role): array;
    
    /**
     * Verifica se existe usuário com o email
     */
    public function emailExists(Email $email): bool;
    
    /**
     * Salva usuário (create ou update)
     */
    public function save(User $user): void;
    
    /**
     * Remove usuário
     */
    public function delete(User $user): void;
    
    /**
     * Busca próximo ID disponível
     */
    public function nextIdentity(): UserId;
}