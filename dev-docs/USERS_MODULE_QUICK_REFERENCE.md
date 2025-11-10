# Módulo de Usuários - Referência Rápida

## 🚀 Guia Rápido

---

## Estrutura Criada

### Value Objects

```php
// Email
$email = Email::fromString('user@example.com');
echo $email->value(); // "user@example.com"

// Phone
$phone = Phone::fromString('(11) 98765-4321');
echo $phone->value();      // "11987654321"
echo $phone->formatted();  // "(11) 98765-4321"

// UserId
$id = UserId::generate();
$id = UserId::fromString($uuid);
```

### Enums

```php
// UserRole
UserRole::ADMIN      // Administrador
UserRole::STAFF      // Funcionário
UserRole::GUARDIAN   // Responsável
UserRole::USER       // Usuário padrão

// UserStatus
UserStatus::ACTIVE      // Ativo
UserStatus::INACTIVE    // Inativo
UserStatus::SUSPENDED   // Suspenso
UserStatus::PENDING     // Pendente
```

### Factory Methods

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

### Business Methods

```php
// Perfil
$user->updateProfile('Novo Nome', $phone);
$user->changeEmail(Email::fromString('novo@email.com'));
$user->changePassword($hashedPassword);

// Status
$user->activate();
$user->deactivate();
$user->suspend();

// Roles
$user->addRole(UserRole::STAFF);
$user->removeRole(UserRole::GUARDIAN);

// Multi-tenant
$user->assignToSchool($schoolId);

// Login
$user->recordLogin();

// Verificações
$user->canLogin();              // bool
$user->hasRole(UserRole::STAFF); // bool
$user->isStaff();               // bool
$user->isGuardian();            // bool
$user->isAdmin();               // bool
```

---

## Use Cases

### Criar Staff

```php
$dto = new CreateStaffDTO(
    email: 'staff@escola.com',
    name: 'João Silva',
    password: 'senha123',
    schoolId: 'school-uuid',
    phone: '11987654321'
);

$response = $createStaffUseCase->execute($dto);
```

### Criar Responsável

```php
$dto = new CreateGuardianDTO(
    email: 'mae@example.com',
    phone: '11987654321',
    name: 'Maria Santos',
    password: 'senha123',
    schoolId: 'school-uuid'
);

$response = $createGuardianUseCase->execute($dto);
// Status: PENDING (aguarda ativação)
```

### Atualizar Usuário

```php
$dto = new UpdateUserDTO(
    userId: 'user-uuid',
    name: 'Nome Atualizado',
    phone: '11999999999',
    email: 'novo@email.com'
);

$response = $updateUserUseCase->execute($dto);
```

### Buscar Usuário

```php
// Por ID
$response = $getUserByIdUseCase->execute($userId);

// Por School
$users = $listUsersBySchoolUseCase->execute($schoolId);
```

### Ativar Responsável

```php
// Staff/Admin ativa responsável pendente
$response = $activateUserUseCase->execute($userId);
```

---

## Repository

```php
// Buscar
$user = $repository->findById(UserId::fromString($id));
$user = $repository->findByEmail(Email::fromString($email));
$users = $repository->findBySchoolId($schoolId);
$users = $repository->findByRole(UserRole::STAFF->value);

// Verificar
$exists = $repository->emailExists(Email::fromString($email));

// Salvar/Deletar
$repository->save($user);
$repository->delete($user);

// Gerar ID
$newId = $repository->nextIdentity();
```

---

## Exceptions

```php
// Usuário não encontrado
try {
    $user = $repository->findById($id);
} catch (UserNotFoundException $e) {
    // Tratar
}

// Email duplicado
try {
    $useCase->execute($dto);
} catch (DuplicateEmailException $e) {
    // Retornar 409 Conflict
}

// Status inválido
try {
    $user->someAction();
} catch (InvalidUserStatusException $e) {
    // Tratar
}
```

---

## DTOs

### Request DTOs

```php
CreateStaffDTO {
    email: string
    name: string
    password: string
    schoolId: string
    phone: ?string
}

CreateGuardianDTO {
    email: string
    phone: string
    name: string
    password: string
    schoolId: string
}

UpdateUserDTO {
    userId: string
    name: ?string
    phone: ?string
    email: ?string
}
```

### Response DTO

```php
UserResponseDTO {
    id: string
    email: string
    name: string
    phone: ?string
    roles: array
    status: string
    schoolId: ?string
    createdAt: DateTimeImmutable
    updatedAt: ?DateTimeImmutable
    lastLoginAt: ?DateTimeImmutable
}

// Converter
$dto = UserResponseDTO::fromEntity($user);
$array = $dto->toArray();
```

---

## Tipos de Usuário

### Admin

- ✅ Todas as permissões
- ✅ Acesso a todas as escolas
- ✅ Status inicial: ACTIVE

### Staff (Funcionário)

- ✅ Gerencia alunos, aulas, presenças
- ✅ Vinculado a uma escola
- ✅ Login por email/senha
- ✅ Status inicial: ACTIVE

### Guardian (Responsável)

- ✅ Visualiza dados dos próprios alunos
- ✅ Vinculado a uma escola
- ✅ Login por email/telefone
- ⚠️ Status inicial: PENDING
- ⚠️ Precisa ser ativado por Staff/Admin

---

## Permissões por Role

### ADMIN
- `*` (todas)

### STAFF
- `students.*`
- `classes.*`
- `attendances.*`
- `evolutions.*`
- `fees.view`
- `payments.view`

### GUARDIAN
- `students.view_own`
- `attendances.view_own`
- `evolutions.view_own`
- `fees.view_own`
- `payments.view_own`
- `payments.create_own`

### USER
- `profile.view`
- `profile.update`

---

## Status de Usuário

| Status | Pode Login? | Descrição |
|--------|-------------|-----------|
| ACTIVE | ✅ Sim | Usuário ativo |
| INACTIVE | ❌ Não | Usuário inativo |
| SUSPENDED | ❌ Não | Usuário suspenso |
| PENDING | ❌ Não | Aguarda aprovação |

---

## Multi-Tenant

### School-based Isolation

```php
// Todos os usuários (exceto Admin) têm schoolId
$user->getSchoolId(); // "school-uuid"

// Buscar usuários da escola
$users = $repository->findBySchoolId($schoolId);

// Header obrigatório em requests
X-School-Id: school-uuid
```

---

## Validações

### Email
- ✅ Formato válido
- ✅ Máximo 255 caracteres
- ✅ Lowercase automático
- ✅ Trim automático
- ✅ Único no sistema

### Phone
- ✅ Formato brasileiro (10 ou 11 dígitos)
- ✅ Normalização automática
- ✅ Formatação para exibição

### UserId
- ✅ UUID v4 válido
- ✅ Geração automática
- ✅ Type-safe

---

## Workflows Comuns

### Workflow 1: Criar Staff

```
1. Validar dados (email, nome, senha)
2. Verificar email único
3. Criar User com factory method
4. Hash da senha
5. Salvar no banco
6. Retornar UserResponseDTO
```

### Workflow 2: Criar e Ativar Responsável

```
1. Responsável se cadastra (status: PENDING)
2. Staff lista responsáveis pendentes
3. Staff visualiza dados e aprova
4. Sistema ativa responsável (status: ACTIVE)
5. Responsável pode fazer login
```

### Workflow 3: Login

```
1. Receber email/senha (ou email/phone)
2. Buscar usuário por email
3. Verificar senha (PasswordHasher)
4. Verificar status.canLogin()
5. Verificar schoolId (multi-tenant)
6. Registrar login (user.recordLogin())
7. Gerar JWT tokens
8. Retornar tokens
```

---

## Testes

### Unit Tests (Value Objects)

```php
public function test_email_is_valid()
{
    $email = Email::fromString('test@example.com');
    $this->assertEquals('test@example.com', $email->value());
}

public function test_invalid_email_throws_exception()
{
    $this->expectException(InvalidArgumentException::class);
    Email::fromString('invalid-email');
}
```

### Use Case Tests

```php
public function test_create_staff_successfully()
{
    $dto = new CreateStaffDTO(...);
    $response = $this->useCase->execute($dto);
    
    $this->assertInstanceOf(UserResponseDTO::class, $response);
    $this->assertEquals('staff@escola.com', $response->email);
}

public function test_duplicate_email_throws_exception()
{
    $this->expectException(DuplicateEmailException::class);
    $this->useCase->execute($dto);
}
```

---

## Configuração

### Services.yaml

```yaml
services:
    # Repository
    App\Domain\Users\UsersRepository:
        class: App\Infrastructure\Persistence\Doctrine\Repository\DoctrineUsersRepository
        
    # Use Cases
    App\Application\UseCase\User\CreateStaffUseCase:
        arguments:
            $usersRepository: '@App\Domain\Users\UsersRepository'
            $passwordHasher: '@Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface'
```

### Migration

```bash
# Gerar migration
php bin/console doctrine:migrations:diff

# Executar
php bin/console doctrine:migrations:migrate
```

---

## Próximos Passos

### Controllers (a implementar)

```php
POST   /api/users/staff       - Criar staff
POST   /api/users/guardians   - Criar responsável
GET    /api/users/{id}        - Buscar usuário
PATCH  /api/users/{id}        - Atualizar usuário
POST   /api/users/{id}/activate - Ativar responsável
GET    /api/users?school_id=  - Listar por escola
```

### Validators (a implementar)

```php
CreateStaffRequest
CreateGuardianRequest
UpdateUserRequest
```

### Testes (a implementar)

```php
EmailTest
PhoneTest
UserIdTest
UserTest
CreateStaffUseCaseTest
DoctrineUsersRepositoryTest
UserControllerTest
```

---

## Links

- **Arquitetura Completa**: [`USERS_MODULE_ARCHITECTURE.md`](USERS_MODULE_ARCHITECTURE.md)
- **Diagramas Visuais**: [`USERS_MODULE_DIAGRAMS.md`](USERS_MODULE_DIAGRAMS.md)
- **README Geral**: [`README.md`](README.md)

---

**Versão**: 1.0  
**Data**: 2025-01-10
