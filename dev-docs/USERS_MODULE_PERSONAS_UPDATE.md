# Módulo de Usuários - Atualização com Personas

## 🎭 Personas Implementadas

### Visão Geral

O módulo de usuários foi **atualizado** para suportar todas as **5 personas** do negócio, cada uma com permissões específicas baseadas em RBAC (Role-Based Access Control).

---

## 📋 Personas e Perfis de Acesso

### 1. 👑 Administrador da Escola (SCHOOL_ADMIN)

**Descrição**: Configura mensalidades e acessa relatórios financeiros

**Características**:
- ✅ Vinculado a uma escola específica (multi-tenant)
- ✅ Status inicial: ACTIVE
- ✅ Login por email/senha
- ✅ Nível hierárquico: 90

**Permissões**:
```php
- school.settings.manage        // Gerenciar configurações
- school.reports.view           // Visualizar relatórios
- school.reports.export         // Exportar relatórios
- fees.*                        // Gestão completa de mensalidades
- payments.*                    // Gestão completa de pagamentos
- users.* (da escola)           // Gerenciar usuários da escola
- students.*                    // Gestão de alunos
- classes.*                     // Gestão de turmas
- attendances.reports           // Relatórios de presença
- evolutions.reports            // Relatórios de evolução
- financial.reports             // Relatórios financeiros
```

**Factory Method**:
```php
$user = User::createSchoolAdmin(
    email: Email::fromString('admin@escola.com'),
    name: 'João Silva',
    hashedPassword: $hash,
    schoolId: 'school-uuid',
    phone: Phone::fromString('11987654321')
);
```

---

### 2. 📋 Secretaria (SECRETARY)

**Descrição**: Gerencia alunos, pagamentos e turmas

**Características**:
- ✅ Vinculado a uma escola específica (multi-tenant)
- ✅ Status inicial: ACTIVE
- ✅ Login por email/senha
- ✅ Nível hierárquico: 70

**Permissões**:
```php
- students.*                    // CRUD de alunos
- students.enroll/unenroll      // Matrículas
- classes.*                     // CRUD de turmas
- classes.manage_enrollments    // Gerenciar matrículas
- payments.*                    // Gestão de pagamentos
- payments.reconcile            // Conciliação
- fees.view/assign              // Ver e atribuir mensalidades
- guardians.*                   // Gestão de responsáveis
- guardians.link_students       // Vincular responsáveis
- payments.reports              // Relatórios de pagamentos
- students.reports              // Relatórios de alunos
```

**Factory Method**:
```php
$user = User::createSecretary(
    email: Email::fromString('secretaria@escola.com'),
    name: 'Maria Santos',
    hashedPassword: $hash,
    schoolId: 'school-uuid',
    phone: Phone::fromString('11987654321')
);
```

---

### 3. 👨‍🏫 Professor (TEACHER)

**Descrição**: Marca presenças e registra observações sobre evolução

**Características**:
- ✅ Vinculado a uma escola específica (multi-tenant)
- ✅ Status inicial: ACTIVE
- ✅ Login por email/senha
- ✅ Nível hierárquico: 50

**Permissões**:
```php
- attendances.*                 // CRUD de presenças
- attendances.mark              // Marcar presença
- evolutions.*                  // CRUD de evoluções
- evolutions.add_observations   // Adicionar observações
- students.view                 // Visualizar alunos
- students.view_details         // Ver detalhes
- classes.view_own              // Ver suas turmas
- classes.view_students         // Ver alunos das turmas
- attendances.reports_own       // Relatórios de suas turmas
- evolutions.reports_own        // Relatórios de suas turmas
```

**Factory Method**:
```php
$user = User::createTeacher(
    email: Email::fromString('professor@escola.com'),
    name: 'Carlos Oliveira',
    hashedPassword: $hash,
    schoolId: 'school-uuid',
    phone: Phone::fromString('11987654321')
);
```

---

### 4. 👨‍👩‍👧 Responsável (GUARDIAN)

**Descrição**: Visualiza histórico e pendências dos filhos

**Características**:
- ✅ Vinculado a uma escola específica (multi-tenant)
- ⚠️ Status inicial: PENDING (aguarda aprovação)
- ✅ Login por email/telefone
- ✅ Nível hierárquico: 30

**Permissões**:
```php
- students.view_own             // Ver apenas seus filhos
- attendances.view_own          // Ver presenças dos filhos
- evolutions.view_own           // Ver evolução dos filhos
- fees.view_own                 // Ver mensalidades
- payments.view_own             // Ver pagamentos
- payments.create_own           // Criar pagamentos
- payments.history_own          // Histórico de pagamentos
- profile.view/update           // Gerenciar próprio perfil
```

**Factory Method**:
```php
$user = User::createGuardian(
    email: Email::fromString('mae@example.com'),
    phone: Phone::fromString('11987654321'),
    name: 'Ana Costa',
    hashedPassword: $hash,
    schoolId: 'school-uuid'
);
// Status: PENDING - Precisa ser ativado por School Admin ou Secretary
```

---

### 5. 🛠️ Suporte SaaS (SAAS_SUPPORT)

**Descrição**: Acesso restrito a metadados sob consentimento

**Características**:
- ❌ NÃO vinculado a escola específica
- ✅ Pode acessar múltiplas escolas (com consentimento)
- ✅ Status inicial: ACTIVE
- ✅ Login por email/senha
- ✅ Nível hierárquico: 100 (mais alto)

**Permissões**:
```php
- system.metadata.view          // Metadados do sistema
- system.logs.view              // Logs técnicos
- system.health.view            // Saúde do sistema
- system.diagnostics.run        // Diagnósticos
- support.tickets.manage        // Gerenciar tickets
// SEM acesso a dados sensíveis dos alunos
```

**Factory Method**:
```php
$user = User::createSaaSSupport(
    email: Email::fromString('suporte@hidro.com'),
    name: 'Equipe Suporte',
    hashedPassword: $hash
);
// schoolId: null - Não vinculado a escola
```

---

## 🔐 Controle de Acesso (RBAC)

### Hierarquia de Permissões

```
100 - SAAS_SUPPORT      (Acesso técnico)
 90 - SCHOOL_ADMIN      (Administrador da Escola)
 70 - SECRETARY         (Secretaria)
 50 - TEACHER           (Professor)
 30 - GUARDIAN          (Responsável)
 10 - USER              (Usuário padrão)
```

### Regras de Gerenciamento

Um usuário pode gerenciar outro se seu nível hierárquico for **maior**:

```php
// School Admin (90) pode gerenciar:
- Secretary (70) ✅
- Teacher (50) ✅
- Guardian (30) ✅

// Secretary (70) pode gerenciar:
- Teacher (50) ✅
- Guardian (30) ✅

// Teacher (50) NÃO pode gerenciar:
- Secretary (70) ❌
- School Admin (90) ❌
```

**Verificação no código**:
```php
if ($admin->canManageUser($teacher)) {
    // Admin pode gerenciar professor
}
```

---

## 🏢 Multi-Tenant

### Isolamento por Escola

Cada requisição autenticada deve conter o header:
```
X-School-Id: school-uuid-123
```

### Vinculação de Usuários

**Vinculados a escola** (school_id obrigatório):
- ✅ SCHOOL_ADMIN
- ✅ SECRETARY
- ✅ TEACHER
- ✅ GUARDIAN

**Não vinculados** (school_id = null):
- ❌ SAAS_SUPPORT (acesso multi-escola)
- ❌ USER (usuário padrão)

**Verificação no código**:
```php
$role = UserRole::SCHOOL_ADMIN;
$isSchoolBound = $role->isSchoolBound(); // true

$user = User::createSchoolAdmin(...);
$isSchoolBound = $user->isSchoolBound(); // true
```

---

## 📊 Métodos Úteis da Entity User

### Verificação de Roles

```php
// Por role específica
$user->hasRole(UserRole::SCHOOL_ADMIN); // bool

// Atalhos por persona
$user->isSchoolAdmin();    // bool
$user->isSecretary();      // bool
$user->isTeacher();        // bool
$user->isGuardian();       // bool
$user->isSaaSSupport();    // bool

// Vinculação a escola
$user->isSchoolBound();    // bool
```

### Verificação de Permissões

```php
// Permissão específica
$user->hasPermission('students.create'); // bool

// Todas as permissões
$permissions = $user->getAllPermissions(); // array

// Gerenciar outro usuário
$admin->canManageUser($teacher); // bool
```

---

## 🔄 Fluxos de Trabalho

### Fluxo 1: Criar Escola e Primeiro Admin

```php
// 1. Criar escola
$school = School::create(...);

// 2. Criar admin da escola
$admin = User::createSchoolAdmin(
    email: Email::fromString('admin@escolanova.com'),
    name: 'João Silva',
    hashedPassword: $hashedPassword,
    schoolId: $school->getId()
);

// 3. Admin pode criar secretária e professores
```

### Fluxo 2: Admin Cria Secretária

```php
// Verificar permissão
if ($admin->hasPermission('users.create')) {
    $secretary = User::createSecretary(
        email: Email::fromString('secretaria@escola.com'),
        name: 'Maria Santos',
        hashedPassword: $hashedPassword,
        schoolId: $admin->getSchoolId() // Mesma escola
    );
}
```

### Fluxo 3: Responsável Se Cadastra

```php
// 1. Responsável faz cadastro (auto-registro)
$guardian = User::createGuardian(
    email: Email::fromString('mae@example.com'),
    phone: Phone::fromString('11987654321'),
    name: 'Ana Costa',
    hashedPassword: $hashedPassword,
    schoolId: 'school-uuid'
);
// Status: PENDING

// 2. Secretária/Admin visualiza pendentes
$pendingUsers = $repository->findByStatus(UserStatus::PENDING);

// 3. Secretária/Admin aprova
$guardian->activate();
$repository->save($guardian);
// Status: ACTIVE - Pode fazer login
```

### Fluxo 4: Professor Marca Presença

```php
// 1. Professor faz login
// 2. Verificar permissão
if ($teacher->hasPermission('attendances.mark')) {
    // 3. Marcar presença
    $attendance = Attendance::create(...);
}
```

---

## 📝 DTOs Criados

### Request DTOs

```php
CreateSchoolAdminDTO {
    email: string
    name: string
    password: string
    schoolId: string
    phone: ?string
}

CreateSecretaryDTO {
    email: string
    name: string
    password: string
    schoolId: string
    phone: ?string
}

CreateTeacherDTO {
    email: string
    name: string
    password: string
    schoolId: string
    phone: ?string
}

CreateGuardianDTO {
    email: string
    phone: string        // Obrigatório para responsável
    name: string
    password: string
    schoolId: string
}
```

---

## 🔒 Segurança e Validações

### Validações por Persona

| Persona | Email | Telefone | School ID | Status Inicial |
|---------|-------|----------|-----------|----------------|
| SCHOOL_ADMIN | ✅ Obrigatório | ➖ Opcional | ✅ Obrigatório | ACTIVE |
| SECRETARY | ✅ Obrigatório | ➖ Opcional | ✅ Obrigatório | ACTIVE |
| TEACHER | ✅ Obrigatório | ➖ Opcional | ✅ Obrigatório | ACTIVE |
| GUARDIAN | ✅ Obrigatório | ✅ Obrigatório | ✅ Obrigatório | PENDING |
| SAAS_SUPPORT | ✅ Obrigatório | ➖ Opcional | ❌ Null | ACTIVE |

### Regras de Aprovação

**Requer aprovação**:
- GUARDIAN (responsável) - Status PENDING → ACTIVE

**Quem pode aprovar**:
- SCHOOL_ADMIN
- SECRETARY

**Não requer aprovação**:
- SCHOOL_ADMIN
- SECRETARY
- TEACHER
- SAAS_SUPPORT

---

## 📚 Documentação Relacionada

- [`USERS_MODULE_ARCHITECTURE.md`](USERS_MODULE_ARCHITECTURE.md) - Arquitetura completa
- [`USERS_MODULE_DIAGRAMS.md`](USERS_MODULE_DIAGRAMS.md) - Diagramas visuais
- [`USERS_MODULE_QUICK_REFERENCE.md`](USERS_MODULE_QUICK_REFERENCE.md) - Referência rápida

---

## ✅ Resumo das Mudanças

### Enum UserRole

- ❌ Removido: `ROLE_ADMIN`, `ROLE_STAFF`
- ✅ Adicionado: 
  - `ROLE_SCHOOL_ADMIN` (Admin da Escola)
  - `ROLE_SECRETARY` (Secretaria)
  - `ROLE_TEACHER` (Professor)
  - `ROLE_SAAS_SUPPORT` (Suporte SaaS)
  - `ROLE_GUARDIAN` (mantido, Responsável)
  - `ROLE_USER` (mantido, Usuário padrão)

### Permissões

✅ **Detalhadas por persona**:
- 17 permissões para SCHOOL_ADMIN
- 14 permissões para SECRETARY
- 11 permissões para TEACHER
- 9 permissões para GUARDIAN
- 5 permissões para SAAS_SUPPORT

### Métodos Novos

✅ **UserRole Enum**:
- `description()` - Descrição da persona
- `isSchoolBound()` - Verifica se vinculado a escola
- `canAccessMultipleSchools()` - Multi-escola
- `requiresApproval()` - Requer aprovação
- `hierarchyLevel()` - Nível hierárquico
- `all()` - Todas as roles
- `schoolAssignable()` - Roles que admin pode atribuir

✅ **User Entity**:
- `createSchoolAdmin()` - Factory method
- `createSecretary()` - Factory method
- `createTeacher()` - Factory method
- `createSaaSSupport()` - Factory method
- `isSchoolAdmin()` - Verificação
- `isSecretary()` - Verificação
- `isTeacher()` - Verificação
- `isSaaSSupport()` - Verificação
- `hasPermission()` - Verifica permissão específica
- `getAllPermissions()` - Todas as permissões
- `canManageUser()` - Hierarquia
- `isSchoolBound()` - Vinculação

### DTOs Criados

✅ 3 novos DTOs:
- `CreateSchoolAdminDTO`
- `CreateSecretaryDTO`
- `CreateTeacherDTO`

### Use Cases Criados

✅ 1 novo Use Case:
- `CreateSchoolAdminUseCase`

---

**Versão**: 2.0  
**Data**: 2025-01-10  
**Projeto**: Hidro API - Módulo de Usuários Atualizado
