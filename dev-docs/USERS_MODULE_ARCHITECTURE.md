# Arquitetura do Módulo de Usuários

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Requisitos Funcionais](#requisitos-funcionais)
3. [Arquitetura em Camadas](#arquitetura-em-camadas)
4. [Estrutura de Diretórios](#estrutura-de-diretórios)
5. [Domínio (Domain Layer)](#domínio-domain-layer)
6. [Aplicação (Application Layer)](#aplicação-application-layer)
7. [Infraestrutura (Infrastructure Layer)](#infraestrutura-infrastructure-layer)
8. [Fluxos de Trabalho](#fluxos-de-trabalho)
9. [Boas Práticas Aplicadas](#boas-práticas-aplicadas)

---

## Visão Geral

O módulo de usuários foi implementado seguindo **Domain-Driven Design (DDD)** e **Arquitetura Hexagonal (Clean Architecture)**, com foco em:

- ✅ **Separação de responsabilidades** (SOLID)
- ✅ **Testabilidade** (TDD-ready)
- ✅ **Multi-tenant** (suporte a múltiplas escolas)
- ✅ **Type-safe** com Value Objects
- ✅ **Business rules** encapsuladas no domínio
- ✅ **Independência** de frameworks

---

## Requisitos Funcionais

### RF-01: Autenticação & Multi-tenant

```
✅ Login por e-mail/senha (Staff)
✅ Login por e-mail/telefone (Responsável)
✅ JWT com refresh token
✅ Expiração curta do access token
✅ Multi-tenant via X-School-Id
✅ Recuperação de senha (estrutura pronta)
✅ Rate-limit em login (a implementar)
```

### Tipos de Usuário

1. **ADMIN** - Administrador do sistema
   - Acesso total
   - Sem vinculação a escola específica

2. **STAFF** - Funcionário da escola
   - Login por email/senha
   - Vinculado a uma escola (school_id)
   - Gerencia alunos, aulas, presenças, evoluções

3. **GUARDIAN** - Responsável pelo aluno
   - Login por email/telefone
   - Vinculado a uma escola (school_id)
   - Status inicial: PENDING (aguarda aprovação)
   - Visualiza dados dos próprios alunos

---

## Arquitetura em Camadas

### Chain of Thought: Como funciona?

```
┌─────────────────────────────────────────────────────────────┐
│  HTTP Request (Presentation Layer)                          │
│  └─ Controller valida dados e extrai school_id              │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│  Application Layer (Use Cases)                              │
│  ├─ Orquestra o fluxo de negócio                           │
│  ├─ Usa DTOs para entrada/saída                            │
│  └─ Chama Domain Services e Repository                     │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│  Domain Layer (Business Logic)                              │
│  ├─ Entities com regras de negócio                         │
│  ├─ Value Objects (Email, Phone, UserId)                   │
│  ├─ Enums (UserRole, UserStatus)                           │
│  ├─ Exceptions (UserNotFoundException, etc.)               │
│  └─ Repository Interfaces (contratos)                      │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│  Infrastructure Layer (Implementação)                       │
│  ├─ Doctrine Repository (persistência)                     │
│  ├─ Password Hasher (segurança)                            │
│  └─ Adapters externos                                      │
└─────────────────────────────────────────────────────────────┘
```

### Princípios SOLID Aplicados

1. **Single Responsibility** - Cada classe tem uma única responsabilidade
2. **Open/Closed** - Extensível via interfaces, fechado para modificação
3. **Liskov Substitution** - Value Objects e DTOs são substituíveis
4. **Interface Segregation** - Repositório com métodos específicos
5. **Dependency Inversion** - Use Cases dependem de abstrações (interfaces)

---

## Estrutura de Diretórios

```
src/
├── Domain/
│   └── Users/
│       ├── ValueObject/
│       │   ├── Email.php              ✅ NOVO
│       │   ├── Phone.php              ✅ NOVO
│       │   └── UserId.php             ✅ NOVO
│       ├── Enum/
│       │   ├── UserRole.php           ✅ NOVO
│       │   └── UserStatus.php         ✅ NOVO
│       ├── Exception/
│       │   ├── UserNotFoundException.php        ✅ NOVO
│       │   ├── DuplicateEmailException.php      ✅ NOVO
│       │   └── InvalidUserStatusException.php   ✅ NOVO
│       ├── User.php                   ✅ REFATORADO
│       └── UsersRepository.php        ✅ REFATORADO
│
├── Application/
│   ├── DTO/User/
│   │   ├── CreateStaffDTO.php         ✅ NOVO
│   │   ├── CreateGuardianDTO.php      ✅ NOVO
│   │   ├── UpdateUserDTO.php          ✅ NOVO
│   │   └── UserResponseDTO.php        ✅ NOVO
│   └── UseCase/User/
│       ├── CreateStaffUseCase.php     ✅ NOVO
│       ├── CreateGuardianUseCase.php  ✅ NOVO
│       ├── GetUserByIdUseCase.php     ✅ NOVO
│       ├── UpdateUserUseCase.php      ✅ NOVO
│       ├── ListUsersBySchoolUseCase.php  ✅ NOVO
│       └── ActivateUserUseCase.php    ✅ NOVO
│
└── Infrastructure/
    └── Persistence/Doctrine/Repository/
        └── DoctrineUsersRepository.php  ✅ NOVO
```

---

## Domínio (Domain Layer)

### 1. Value Objects

Value Objects garantem **imutabilidade** e **validação** dos dados.

#### Email.php

```php
$email = Email::fromString('usuario@example.com');
echo $email->value(); // "usuario@example.com"

// Validações automáticas:
// - Formato válido de email
// - Normalização (lowercase, trim)
// - Comprimento máximo
```

**Por que usar?**
- ✅ Email sempre válido
- ✅ Impossível criar email inválido
- ✅ Comparação type-safe
- ✅ Regras de negócio encapsuladas

#### Phone.php

```php
$phone = Phone::fromString('(11) 98765-4321');
echo $phone->value();      // "11987654321" (normalizado)
echo $phone->formatted();  // "(11) 98765-4321"

// Validações automáticas:
// - Formato brasileiro (10 ou 11 dígitos)
// - Normalização (remove caracteres)
```

#### UserId.php

```php
$id = UserId::generate();              // Gera UUID v4
$id = UserId::fromString($uuidString); // Carrega existente

echo $id->value(); // "550e8400-e29b-41d4-a716-446655440000"

// Validações automáticas:
// - UUID v4 válido
// - Type-safe
```

### 2. Enums

Enums garantem **valores fixos** e **type-safety**.

#### UserRole.php

```php
enum UserRole: string
{
    case ADMIN = 'ROLE_ADMIN';
    case STAFF = 'ROLE_STAFF';
    case GUARDIAN = 'ROLE_GUARDIAN';
    case USER = 'ROLE_USER';
}

// Uso:
$role = UserRole::STAFF;
echo $role->label();        // "Funcionário"
$perms = $role->permissions(); // Array de permissões
```

**Permissões por Role:**

| Role | Permissões |
|------|------------|
| ADMIN | Todas (*) |
| STAFF | students.*, classes.*, attendances.*, fees.view |
| GUARDIAN | *_own (apenas próprios dados) |
| USER | profile.* |

#### UserStatus.php

```php
enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case PENDING = 'pending';
}

// Uso:
$status = UserStatus::ACTIVE;
echo $status->label();    // "Ativo"
$can = $status->canLogin(); // true
```

### 3. Entidade User (Aggregate Root)

A entidade `User` é o **agregado raiz** do módulo.

#### Factory Methods (Named Constructors)

```php
// Criar Staff
$user = User::createStaff(
    email: Email::fromString('staff@escola.com'),
    name: 'João Silva',
    hashedPassword: $hash,
    schoolId: 'school-uuid',
    phone: Phone::fromString('11987654321')
);

// Criar Responsável
$user = User::createGuardian(
    email: Email::fromString('mae@example.com'),
    phone: Phone::fromString('11987654321'),
    name: 'Maria Santos',
    hashedPassword: $hash,
    schoolId: 'school-uuid'
);

// Criar Admin
$user = User::createAdmin(
    email: Email::fromString('admin@hidro.com'),
    name: 'Administrador',
    hashedPassword: $hash
);
```

**Por que Factory Methods?**
- ✅ Intenção clara (createStaff vs createGuardian)
- ✅ Garante invariantes de cada tipo
- ✅ Encapsula lógica de criação
- ✅ Type-safe com Value Objects

#### Métodos de Negócio

```php
// Atualizar perfil
$user->updateProfile('Novo Nome', $phone);

// Alterar email
$user->changeEmail(Email::fromString('novo@email.com'));

// Alterar senha
$user->changePassword($hashedPassword);

// Gerenciar status
$user->activate();
$user->deactivate();
$user->suspend();

// Gerenciar roles
$user->addRole(UserRole::STAFF);
$user->removeRole(UserRole::GUARDIAN);

// Multi-tenant
$user->assignToSchool($schoolId);

// Login
$user->recordLogin(); // Registra último acesso

// Verificações
$user->canLogin();              // bool
$user->hasRole(UserRole::STAFF); // bool
$user->isStaff();               // bool
$user->isGuardian();            // bool
```

### 4. Repository Interface

```php
interface UsersRepository
{
    public function findById(UserId $id): ?User;
    public function findByEmail(Email $email): ?User;
    public function findBySchoolId(string $schoolId): array;
    public function findByRole(string $role): array;
    public function emailExists(Email $email): bool;
    public function save(User $user): void;
    public function delete(User $user): void;
    public function nextIdentity(): UserId;
}
```

**Benefícios:**
- ✅ Independente de Doctrine
- ✅ Fácil de testar (mock)
- ✅ Pode trocar implementação (MongoDB, API, etc.)

### 5. Exceptions

```php
// Usuário não encontrado
throw UserNotFoundException::withId($id);
throw UserNotFoundException::withEmail($email);

// Email duplicado
throw DuplicateEmailException::withEmail($email);

// Status inválido
throw InvalidUserStatusException::cannotLogin($status);
throw InvalidUserStatusException::cannotPerformAction($action, $status);
```

**Benefícios:**
- ✅ Exceptions de domínio (DomainException)
- ✅ Mensagens descritivas
- ✅ Type-safe
- ✅ Fácil tratamento na camada de apresentação

---

## Aplicação (Application Layer)

### DTOs (Data Transfer Objects)

DTOs transportam dados entre camadas sem lógica de negócio.

#### CreateStaffDTO.php

```php
$dto = new CreateStaffDTO(
    email: 'staff@escola.com',
    name: 'João Silva',
    password: 'senha123',
    schoolId: 'school-uuid',
    phone: '11987654321'
);
```

#### UserResponseDTO.php

```php
$response = UserResponseDTO::fromEntity($user);
$array = $response->toArray();

// Resultado:
[
    'id' => '550e8400-...',
    'email' => 'user@example.com',
    'name' => 'João Silva',
    'phone' => '11987654321',
    'roles' => ['ROLE_STAFF', 'ROLE_USER'],
    'status' => 'active',
    'school_id' => 'school-uuid',
    'created_at' => '2025-01-10 12:00:00',
    'updated_at' => null,
    'last_login_at' => null
]
```

### Use Cases

Use Cases orquestram o fluxo de negócio.

#### CreateStaffUseCase

```php
class CreateStaffUseCase
{
    public function execute(CreateStaffDTO $dto): UserResponseDTO
    {
        // 1. Valida email único
        // 2. Cria usuário com factory method
        // 3. Hash da senha
        // 4. Persiste
        // 5. Retorna DTO
    }
}
```

#### CreateGuardianUseCase

```php
class CreateGuardianUseCase
{
    public function execute(CreateGuardianDTO $dto): UserResponseDTO
    {
        // Cria responsável com status PENDING
        // Aguarda ativação por Staff/Admin
    }
}
```

#### UpdateUserUseCase

```php
$dto = new UpdateUserDTO(
    userId: 'user-uuid',
    name: 'Nome Atualizado',
    phone: '11999999999'
);

$response = $useCase->execute($dto);
```

#### ActivateUserUseCase

```php
// Staff/Admin ativa responsável pendente
$response = $useCase->execute($userId);
```

---

## Infraestrutura (Infrastructure Layer)

### DoctrineUsersRepository

Implementação Doctrine do repositório:

```php
class DoctrineUsersRepository implements UsersRepository
{
    public function findById(UserId $id): ?User
    {
        return $this->find($id->value());
    }

    public function findByEmail(Email $email): ?User
    {
        return $this->findOneBy(['email' => $email->value()]);
    }

    public function emailExists(Email $email): bool
    {
        return $this->count(['email' => $email->value()]) > 0;
    }

    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
```

**Benefícios:**
- ✅ Adapta Doctrine para interface de domínio
- ✅ Conversão automática de Value Objects
- ✅ Fácil de trocar por outra implementação

---

## Fluxos de Trabalho

### Fluxo 1: Criar Usuário Staff

```
1. Controller recebe request
   ├─ Valida dados (Symfony Validator)
   └─ Extrai school_id do header X-School-Id

2. Controller chama CreateStaffUseCase
   └─ Passa CreateStaffDTO

3. Use Case executa
   ├─ Valida email único (repository)
   ├─ Cria User com factory method
   ├─ Hash da senha (PasswordHasher)
   └─ Persiste (repository.save)

4. Use Case retorna UserResponseDTO

5. Controller retorna JSON
   └─ Status 201 Created
```

### Fluxo 2: Login de Responsável

```
1. Controller recebe request
   ├─ email
   ├─ phone (opcional)
   └─ password

2. Autentica via JWT (AuthenticateUserUseCase)
   ├─ Busca user por email
   ├─ Verifica senha
   ├─ Verifica status.canLogin()
   ├─ Verifica school_id (multi-tenant)
   └─ Registra login (user.recordLogin)

3. Gera tokens
   ├─ Access token (1h)
   └─ Refresh token (7 dias)

4. Retorna tokens
```

### Fluxo 3: Ativar Responsável Pendente

```
1. Staff/Admin acessa sistema

2. Lista responsáveis pendentes
   └─ repository.findByStatus(PENDING)

3. Ativa responsável
   ├─ ActivateUserUseCase
   ├─ user.activate()
   └─ repository.save()

4. Responsável pode fazer login
```

---

## Boas Práticas Aplicadas

### 1. SOLID

✅ **Single Responsibility**
- User: Representa usuário
- UsersRepository: Persistência
- CreateStaffUseCase: Criar staff
- Email: Validar email

✅ **Open/Closed**
- Novos Use Cases sem modificar existentes
- Novos tipos de usuário via factory methods

✅ **Liskov Substitution**
- Value Objects intercambiáveis
- Repository pode ser substituído (mock, API, etc.)

✅ **Interface Segregation**
- Repository com métodos específicos
- Não força implementação desnecessária

✅ **Dependency Inversion**
- Use Cases dependem de interfaces
- Não dependem de Doctrine diretamente

### 2. Clean Code

✅ **Nomes Descritivos**
```php
// ❌ Ruim
public function create($data);

// ✅ Bom
public function createStaff(CreateStaffDTO $dto): UserResponseDTO;
```

✅ **Métodos Pequenos**
- Factory methods focados
- Use Cases com responsabilidade única

✅ **Sem Magic Numbers**
```php
// ❌ Ruim
if (strlen($email) > 255) throw ...

// ✅ Bom (na classe Email)
private const MAX_LENGTH = 255;
```

### 3. Type Safety

✅ **Value Objects**
```php
// ❌ Ruim
public function findByEmail(string $email): ?User;

// ✅ Bom
public function findByEmail(Email $email): ?User;
```

✅ **Enums**
```php
// ❌ Ruim
$role = 'ROLE_STAFF'; // String pode ser qualquer coisa

// ✅ Bom
$role = UserRole::STAFF; // Type-safe
```

### 4. Testabilidade

✅ **Interfaces**
```php
// Mock fácil
$repository = $this->createMock(UsersRepository::class);
```

✅ **Injeção de Dependência**
```php
class CreateStaffUseCase
{
    public function __construct(
        private UsersRepository $repository,
        private PasswordHasher $hasher
    ) {}
}
```

✅ **Factory Methods**
```php
// Fácil criar usuário para testes
$user = User::createStaff(...);
```

### 5. Domain-Driven Design

✅ **Ubiquitous Language**
- Staff (não Employee)
- Guardian (não Parent)
- School (não Tenant)

✅ **Aggregates**
- User é aggregate root
- Encapsula regras de negócio

✅ **Value Objects**
- Email, Phone, UserId
- Imutáveis e validados

✅ **Repositories**
- Abstraem persistência
- Focados no domínio

---

## Checklist de Implementação

### ✅ Concluído

- [x] Value Objects (Email, Phone, UserId)
- [x] Enums (UserRole, UserStatus)
- [x] Exceptions (UserNotFoundException, etc.)
- [x] Entidade User refatorada (DDD)
- [x] UsersRepository interface melhorada
- [x] DoctrineUsersRepository implementado
- [x] DTOs (Create, Update, Response)
- [x] Use Cases principais (Create, Get, Update, List, Activate)
- [x] Documentação completa

### 🔄 Próximos Passos

- [ ] Controllers (Presentation Layer)
- [ ] Request Validators (Symfony Validator)
- [ ] Testes Unitários (Domain)
- [ ] Testes de Integração (Repository)
- [ ] Testes Funcionais (Use Cases)
- [ ] Migration do banco de dados
- [ ] Configuração de serviços (services.yaml)
- [ ] Rate Limiting no login
- [ ] Recuperação de senha (Use Case + Email)
- [ ] Auditoria de ações

---

## Resumo

### Estrutura Criada

**Domain Layer:**
- 3 Value Objects
- 2 Enums
- 3 Exceptions
- 1 Entity (refatorada)
- 1 Repository Interface (melhorada)

**Application Layer:**
- 4 DTOs
- 6 Use Cases

**Infrastructure Layer:**
- 1 Repository Implementation

**Total:** 21 arquivos criados/refatorados

### Princípios Seguidos

✅ DDD - Domain-Driven Design
✅ Clean Architecture
✅ SOLID
✅ Clean Code
✅ Type Safety
✅ Testability
✅ Multi-tenant

### Próxima Etapa

Implementar Controllers e Validators para completar o ciclo HTTP → Use Case → Repository → Response.

---

**Criado em**: 2025-01-10  
**Versão**: 1.0  
**Projeto**: Hidro API - Módulo de Usuários
