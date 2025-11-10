<?php

declare(strict_types=1);

namespace App\Domain\Users;

use App\Domain\Common\AggregateRoot;
use App\Domain\Users\Enum\UserRole;
use App\Domain\Users\Enum\UserStatus;
use App\Domain\Users\ValueObject\Email;
use App\Domain\Users\ValueObject\Phone;
use App\Domain\Users\ValueObject\UserId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Entidade User - Agregado Raiz
 * 
 * Representa um usuário do sistema (Staff ou Responsável)
 * Seguindo princípios de DDD e Clean Architecture
 */
#[ORM\Entity(repositoryClass: \App\Infrastructure\Persistence\Doctrine\DoctrineUsersRepository::class)]
#[ORM\Table(name: 'users')]
class User implements AggregateRoot, UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;
    
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;
    
    #[ORM\Column(type: 'string', length: 255)]
    private string $password;
    
    #[ORM\Column(type: 'string', length: 255)]
    private string $name;
    
    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $phone = null;
    
    #[ORM\Column(type: 'json')]
    private array $roles = [];
    
    #[ORM\Column(type: 'string', length: 20)]
    private string $status;
    
    #[ORM\Column(type: 'string', length: 36, nullable: true)]
    private ?string $schoolId = null;
    
    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;
    
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;
    
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $lastLoginAt = null;
    
    /**
     * Factory Method para criar Administrador da Escola
     */
    public static function createSchoolAdmin(
        Email $email,
        string $name,
        string $hashedPassword,
        string $schoolId,
        ?Phone $phone = null
    ): self {
        return new self(
            UserId::generate(),
            $email,
            $name,
            $hashedPassword,
            [UserRole::SCHOOL_ADMIN->value],
            UserStatus::ACTIVE,
            $schoolId,
            $phone
        );
    }
    
    /**
     * Factory Method para criar Secretária
     */
    public static function createSecretary(
        Email $email,
        string $name,
        string $hashedPassword,
        string $schoolId,
        ?Phone $phone = null
    ): self {
        return new self(
            UserId::generate(),
            $email,
            $name,
            $hashedPassword,
            [UserRole::SECRETARY->value],
            UserStatus::ACTIVE,
            $schoolId,
            $phone
        );
    }
    
    /**
     * Factory Method para criar Professor
     */
    public static function createTeacher(
        Email $email,
        string $name,
        string $hashedPassword,
        string $schoolId,
        ?Phone $phone = null
    ): self {
        return new self(
            UserId::generate(),
            $email,
            $name,
            $hashedPassword,
            [UserRole::TEACHER->value],
            UserStatus::ACTIVE,
            $schoolId,
            $phone
        );
    }
    
    /**
     * Factory Method para criar Responsável
     */
    public static function createGuardian(
        Email $email,
        Phone $phone,
        string $name,
        string $hashedPassword,
        string $schoolId
    ): self {
        return new self(
            UserId::generate(),
            $email,
            $name,
            $hashedPassword,
            [UserRole::GUARDIAN->value],
            UserStatus::PENDING, // Responsável começa pendente
            $schoolId,
            $phone
        );
    }
    
    /**
     * Factory Method para criar Suporte SaaS
     */
    public static function createSaaSSupport(
        Email $email,
        string $name,
        string $hashedPassword
    ): self {
        return new self(
            UserId::generate(),
            $email,
            $name,
            $hashedPassword,
            [UserRole::SAAS_SUPPORT->value],
            UserStatus::ACTIVE,
            null, // Suporte não tem escola específica
            null
        );
    }
    
    /**
     * Construtor privado - Use os Factory Methods
     */
    private function __construct(
        UserId $id,
        Email $email,
        string $name,
        string $hashedPassword,
        array $roles,
        UserStatus $status,
        ?string $schoolId,
        ?Phone $phone
    ) {
        $this->id = $id->value();
        $this->email = $email->value();
        $this->name = $name;
        $this->password = $hashedPassword;
        $this->roles = $roles;
        $this->status = $status->value;
        $this->schoolId = $schoolId;
        $this->phone = $phone?->value();
        $this->createdAt = new DateTimeImmutable();
    }
    
    // ===== Getters =====
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getUserId(): UserId
    {
        return UserId::fromString($this->id);
    }
    
    public function getEmail(): Email
    {
        return Email::fromString($this->email);
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getPhone(): ?Phone
    {
        return $this->phone ? Phone::fromString($this->phone) : null;
    }
    
    public function getStatus(): UserStatus
    {
        return UserStatus::from($this->status);
    }
    
    public function getSchoolId(): ?string
    {
        return $this->schoolId;
    }
    
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
    
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
    
    public function getLastLoginAt(): ?DateTimeImmutable
    {
        return $this->lastLoginAt;
    }
    
    // ===== Business Methods =====
    
    /**
     * Atualiza as informações do usuário
     */
    public function updateProfile(string $name, ?Phone $phone = null): void
    {
        $this->name = $name;
        $this->phone = $phone?->value();
        $this->updatedAt = new DateTimeImmutable();
    }
    
    /**
     * Altera o email do usuário
     */
    public function changeEmail(Email $newEmail): void
    {
        $this->email = $newEmail->value();
        $this->updatedAt = new DateTimeImmutable();
    }
    
    /**
     * Altera a senha do usuário
     */
    public function changePassword(string $hashedPassword): void
    {
        $this->password = $hashedPassword;
        $this->updatedAt = new DateTimeImmutable();
    }
    
    /**
     * Ativa o usuário
     */
    public function activate(): void
    {
        $this->status = UserStatus::ACTIVE->value;
        $this->updatedAt = new DateTimeImmutable();
    }
    
    /**
     * Desativa o usuário
     */
    public function deactivate(): void
    {
        $this->status = UserStatus::INACTIVE->value;
        $this->updatedAt = new DateTimeImmutable();
    }
    
    /**
     * Suspende o usuário
     */
    public function suspend(): void
    {
        $this->status = UserStatus::SUSPENDED->value;
        $this->updatedAt = new DateTimeImmutable();
    }
    
    /**
     * Adiciona uma role ao usuário
     */
    public function addRole(UserRole $role): void
    {
        if (!in_array($role->value, $this->roles, true)) {
            $this->roles[] = $role->value;
            $this->updatedAt = new DateTimeImmutable();
        }
    }
    
    /**
     * Remove uma role do usuário
     */
    public function removeRole(UserRole $role): void
    {
        $this->roles = array_values(
            array_filter(
                $this->roles,
                fn($r) => $r !== $role->value
            )
        );
        $this->updatedAt = new DateTimeImmutable();
    }
    
    /**
     * Atribui o usuário a uma escola (multi-tenant)
     */
    public function assignToSchool(string $schoolId): void
    {
        $this->schoolId = $schoolId;
        $this->updatedAt = new DateTimeImmutable();
    }
    
    /**
     * Registra o último login
     */
    public function recordLogin(): void
    {
        $this->lastLoginAt = new DateTimeImmutable();
    }
    
    /**
     * Verifica se o usuário pode fazer login
     */
    public function canLogin(): bool
    {
        return $this->getStatus()->canLogin();
    }
    
    /**
     * Verifica se o usuário tem uma role específica
     */
    public function hasRole(UserRole $role): bool
    {
        return in_array($role->value, $this->roles, true);
    }
    
    /**
     * Verifica se é administrador da escola
     */
    public function isSchoolAdmin(): bool
    {
        return $this->hasRole(UserRole::SCHOOL_ADMIN);
    }
    
    /**
     * Verifica se é secretária
     */
    public function isSecretary(): bool
    {
        return $this->hasRole(UserRole::SECRETARY);
    }
    
    /**
     * Verifica se é professor
     */
    public function isTeacher(): bool
    {
        return $this->hasRole(UserRole::TEACHER);
    }
    
    /**
     * Verifica se é responsável
     */
    public function isGuardian(): bool
    {
        return $this->hasRole(UserRole::GUARDIAN);
    }
    
    /**
     * Verifica se é suporte SaaS
     */
    public function isSaaSSupport(): bool
    {
        return $this->hasRole(UserRole::SAAS_SUPPORT);
    }
    
    /**
     * Verifica se o usuário está vinculado a uma escola
     */
    public function isSchoolBound(): bool
    {
        return $this->schoolId !== null;
    }
    
    /**
     * Verifica se o usuário tem permissão específica
     */
    public function hasPermission(string $permission): bool
    {
        foreach ($this->roles as $roleValue) {
            $role = UserRole::fromString($roleValue);
            if (in_array($permission, $role->permissions(), true) || 
                in_array('*', $role->permissions(), true)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Retorna todas as permissões do usuário
     */
    public function getAllPermissions(): array
    {
        $permissions = [];
        foreach ($this->roles as $roleValue) {
            $role = UserRole::fromString($roleValue);
            $permissions = array_merge($permissions, $role->permissions());
        }
        return array_unique($permissions);
    }
    
    /**
     * Verifica se pode gerenciar outro usuário baseado na hierarquia
     */
    public function canManageUser(User $otherUser): bool
    {
        $myHighestLevel = $this->getHighestHierarchyLevel();
        $otherHighestLevel = $otherUser->getHighestHierarchyLevel();
        
        return $myHighestLevel > $otherHighestLevel;
    }
    
    /**
     * Retorna o nível hierárquico mais alto do usuário
     */
    private function getHighestHierarchyLevel(): int
    {
        $maxLevel = 0;
        foreach ($this->roles as $roleValue) {
            $role = UserRole::fromString($roleValue);
            $level = $role->hierarchyLevel();
            if ($level > $maxLevel) {
                $maxLevel = $level;
            }
        }
        return $maxLevel;
    }
    
    // ===== Symfony UserInterface =====
    
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Garante que todo usuário tenha pelo menos ROLE_USER
        $roles[] = UserRole::USER->value;
        
        return array_unique($roles);
    }
    
    public function getPassword(): string
    {
        return $this->password;
    }
    
    public function getUserIdentifier(): string
    {
        return $this->email;
    }
    
    public function eraseCredentials(): void
    {
        // Limpa dados sensíveis temporários, se houver
    }
}