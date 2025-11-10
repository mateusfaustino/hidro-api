<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Users\Enum\UserRole;
use App\Domain\Users\Enum\UserStatus;
use App\Domain\Users\User;
use App\Domain\Users\UsersRepository;
use App\Domain\Users\ValueObject\Email;
use App\Domain\Users\ValueObject\Phone;
use App\Domain\Users\ValueObject\UserId;
use Doctrine\DBAL\Connection;
use DateTimeImmutable;

/**
 * Implementação Doctrine do Repositório de Usuários
 * 
 * Camada de Infraestrutura - usa DBAL para evitar problemas com mapeamento ORM
 */
final class DoctrineUsersRepository implements UsersRepository
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function findById(UserId $id): ?User
    {
        $data = $this->connection->fetchAssociative(
            'SELECT * FROM users WHERE id = ?',
            [$id->value()]
        );
        
        return $data ? $this->hydrateUser($data) : null;
    }

    public function findByEmail(Email $email): ?User
    {
        $data = $this->connection->fetchAssociative(
            'SELECT * FROM users WHERE email = ?',
            [$email->value()]
        );
        
        return $data ? $this->hydrateUser($data) : null;
    }

    public function findBySchoolId(string $schoolId): array
    {
        $results = $this->connection->fetchAllAssociative(
            'SELECT * FROM users WHERE school_id = ? ORDER BY created_at DESC',
            [$schoolId]
        );
        
        return array_map(fn($data) => $this->hydrateUser($data), $results);
    }

    public function findBySchoolIdPaginated(
        string $schoolId,
        int $limit,
        int $offset,
        ?string $sortBy = 'created_at',
        string $sortOrder = 'DESC',
        ?string $role = null,
        ?string $status = null,
        ?string $search = null
    ): array {
        // Valida ordem
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        // Valida coluna de ordenação
        $allowedSortColumns = ['name', 'email', 'created_at', 'status'];
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'created_at';
        
        // Constrói WHERE clause
        $where = ['school_id = ?'];
        $params = [$schoolId];
        
        // Filtro por role
        if ($role !== null) {
            $where[] = 'JSON_CONTAINS(roles, ?)';
            $params[] = json_encode($role);
        }
        
        // Filtro por status
        if ($status !== null) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        
        // Busca por nome ou email
        if ($search !== null && $search !== '') {
            $where[] = '(name LIKE ? OR email LIKE ?)';
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Conta total
        $total = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM users WHERE {$whereClause}",
            $params
        );
        
        // Busca dados paginados
        $results = $this->connection->fetchAllAssociative(
            "SELECT * FROM users WHERE {$whereClause} ORDER BY {$sortBy} {$sortOrder} LIMIT ? OFFSET ?",
            array_merge($params, [$limit, $offset])
        );
        
        $users = array_map(fn($data) => $this->hydrateUser($data), $results);
        
        return [
            'users' => $users,
            'total' => $total
        ];
    }

    public function findByRole(string $role): array
    {
        $results = $this->connection->fetchAllAssociative(
            'SELECT * FROM users WHERE JSON_CONTAINS(roles, ?)',
            [json_encode($role)]
        );
        
        return array_map(fn($data) => $this->hydrateUser($data), $results);
    }

    public function emailExists(Email $email): bool
    {
        $count = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM users WHERE email = ?',
            [$email->value()]
        );
        
        return $count > 0;
    }

    public function save(User $user): void
    {
        $data = [
            'id' => $user->getId(),
            'email' => $user->getEmail()->value(),
            'name' => $user->getName(),
            'phone' => $user->getPhone()?->value(),
            'password' => $user->getPassword(),
            'roles' => json_encode($user->getRoles()), // getRoles() já retorna array de strings
            'status' => $user->getStatus()->value,
            'school_id' => $user->getSchoolId(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->getUpdatedAt()?->format('Y-m-d H:i:s'),
            'last_login_at' => $user->getLastLoginAt()?->format('Y-m-d H:i:s'),
        ];
        
        // Verifica se já existe
        $exists = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM users WHERE id = ?',
            [$user->getId()]
        );
        
        if ($exists) {
            // Update
            $this->connection->update('users', $data, ['id' => $user->getId()]);
        } else {
            // Insert
            $this->connection->insert('users', $data);
        }
    }

    public function delete(User $user): void
    {
        $this->connection->delete('users', ['id' => $user->getId()]);
    }

    public function nextIdentity(): UserId
    {
        return UserId::generate();
    }
    
    private function hydrateUser(array $data): User
    {
        $email = Email::fromString($data['email']);
        $phone = $data['phone'] ? Phone::fromString($data['phone']) : null;
        $roles = array_map(
            fn($role) => UserRole::fromString($role),
            json_decode($data['roles'], true)
        );
        $status = UserStatus::from($data['status']);
        
        // Usa reflection para criar o User (já que o construtor é privado)
        $reflection = new \ReflectionClass(User::class);
        $user = $reflection->newInstanceWithoutConstructor();
        
        // Define propriedades usando reflection
        $this->setProperty($user, 'id', $data['id']);
        $this->setProperty($user, 'email', $data['email']);
        $this->setProperty($user, 'name', $data['name']);
        $this->setProperty($user, 'phone', $data['phone']);
        $this->setProperty($user, 'password', $data['password']);
        $this->setProperty($user, 'roles', json_decode($data['roles'], true));
        $this->setProperty($user, 'status', $data['status']);
        $this->setProperty($user, 'schoolId', $data['school_id']);
        $this->setProperty($user, 'createdAt', new DateTimeImmutable($data['created_at']));
        $this->setProperty($user, 'updatedAt', $data['updated_at'] ? new DateTimeImmutable($data['updated_at']) : null);
        $this->setProperty($user, 'lastLoginAt', $data['last_login_at'] ? new DateTimeImmutable($data['last_login_at']) : null);
        
        return $user;
    }
    
    private function setProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new \ReflectionProperty($object, $property);
        $reflection->setAccessible(true);
        $reflection->setValue($object, $value);
    }
}
