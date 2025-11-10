# Diagramas do Módulo de Usuários

## 📊 Diagramas Visuais

---

## 1. Arquitetura em Camadas

```mermaid
graph TB
    subgraph "Presentation Layer"
        A[HTTP Request] --> B[UserController]
        B --> C[Validation]
    end
    
    subgraph "Application Layer"
        C --> D[CreateStaffUseCase]
        C --> E[CreateGuardianUseCase]
        C --> F[UpdateUserUseCase]
        C --> G[GetUserByIdUseCase]
        
        D --> H[DTOs]
        E --> H
        F --> H
        G --> H
    end
    
    subgraph "Domain Layer"
        H --> I[User Entity]
        I --> J[Value Objects]
        I --> K[Enums]
        I --> L[Business Rules]
        
        D --> M[UsersRepository Interface]
        E --> M
        F --> M
        G --> M
    end
    
    subgraph "Infrastructure Layer"
        M --> N[DoctrineUsersRepository]
        N --> O[(Database)]
    end
    
    B --> P[HTTP Response JSON]
```

---

## 2. Estrutura de Domínio (Domain Layer)

```mermaid
classDiagram
    class User {
        -string id
        -string email
        -string password
        -string name
        -string phone
        -array roles
        -string status
        -string schoolId
        -DateTimeImmutable createdAt
        +createStaff() User
        +createGuardian() User
        +createAdmin() User
        +updateProfile()
        +changeEmail()
        +changePassword()
        +activate()
        +deactivate()
        +suspend()
        +canLogin() bool
        +isStaff() bool
        +isGuardian() bool
    }
    
    class Email {
        -string value
        +fromString() Email
        +value() string
        +equals() bool
    }
    
    class Phone {
        -string value
        +fromString() Phone
        +value() string
        +formatted() string
    }
    
    class UserId {
        -string value
        +generate() UserId
        +fromString() UserId
        +value() string
    }
    
    class UserRole {
        <<enumeration>>
        ADMIN
        STAFF
        GUARDIAN
        USER
        +label() string
        +permissions() array
    }
    
    class UserStatus {
        <<enumeration>>
        ACTIVE
        INACTIVE
        SUSPENDED
        PENDING
        +canLogin() bool
    }
    
    class UsersRepository {
        <<interface>>
        +findById() User
        +findByEmail() User
        +findBySchoolId() array
        +emailExists() bool
        +save()
        +delete()
    }
    
    User --> Email
    User --> Phone
    User --> UserId
    User --> UserRole
    User --> UserStatus
    User --> UsersRepository
```

---

## 3. Fluxo de Criação de Staff

```mermaid
sequenceDiagram
    participant C as Controller
    participant UC as CreateStaffUseCase
    participant VO as Value Objects
    participant U as User
    participant PH as PasswordHasher
    participant R as Repository
    participant DB as Database
    
    C->>UC: execute(CreateStaffDTO)
    UC->>VO: Email::fromString()
    VO-->>UC: Email
    UC->>R: emailExists(Email)
    R-->>UC: false
    UC->>VO: Phone::fromString()
    VO-->>UC: Phone
    UC->>U: createStaff(email, name, ...)
    U-->>UC: User (status=ACTIVE)
    UC->>PH: hashPassword(user, password)
    PH-->>UC: hashedPassword
    UC->>U: changePassword(hashedPassword)
    UC->>R: save(User)
    R->>DB: persist + flush
    DB-->>R: OK
    R-->>UC: void
    UC->>VO: UserResponseDTO::fromEntity(User)
    VO-->>UC: UserResponseDTO
    UC-->>C: UserResponseDTO
    C-->>Client: JSON 201 Created
```

---

## 4. Fluxo de Criação de Responsável

```mermaid
sequenceDiagram
    participant C as Controller
    participant UC as CreateGuardianUseCase
    participant U as User
    participant R as Repository
    
    C->>UC: execute(CreateGuardianDTO)
    Note over UC: Valida email + telefone
    UC->>U: createGuardian(...)
    Note over U: Status inicial: PENDING
    U-->>UC: User (PENDING)
    UC->>R: save(User)
    UC-->>C: UserResponseDTO
    
    Note over C: Responsável criado<br/>Aguarda ativação
```

---

## 5. Fluxo de Ativação de Responsável

```mermaid
graph TB
    A[Responsável se cadastra] --> B[Status: PENDING]
    B --> C[Staff visualiza pendentes]
    C --> D{Aprovar?}
    D -->|Sim| E[ActivateUserUseCase]
    D -->|Não| F[Rejeitar/Suspender]
    E --> G[user.activate]
    G --> H[Status: ACTIVE]
    H --> I[Responsável pode fazer login]
    F --> J[Status: SUSPENDED]
```

---

## 6. Hierarquia de Value Objects

```mermaid
graph TB
    subgraph "Value Objects Pattern"
        A[Primitive String] --> B[Email VO]
        A --> C[Phone VO]
        A --> D[UserId VO]
        
        B --> E[Validação Automática]
        C --> E
        D --> E
        
        E --> F[Type-Safety]
        E --> G[Imutabilidade]
        E --> H[Comparação Segura]
        
        F --> I[Código Robusto]
        G --> I
        H --> I
    end
```

---

## 7. Roles e Permissões

```mermaid
graph LR
    A[ADMIN] --> B[Todas Permissões]
    
    C[STAFF] --> D[students.*]
    C --> E[classes.*]
    C --> F[attendances.*]
    C --> G[fees.view]
    
    H[GUARDIAN] --> I[students.view_own]
    H --> J[attendances.view_own]
    H --> K[fees.view_own]
    H --> L[payments.create_own]
    
    M[USER] --> N[profile.view]
    M --> O[profile.update]
```

---

## 8. Estados de Usuário (Status)

```mermaid
stateDiagram-v2
    [*] --> PENDING: createGuardian()
    [*] --> ACTIVE: createStaff() / createAdmin()
    
    PENDING --> ACTIVE: activate()
    PENDING --> SUSPENDED: suspend()
    
    ACTIVE --> INACTIVE: deactivate()
    ACTIVE --> SUSPENDED: suspend()
    
    INACTIVE --> ACTIVE: activate()
    INACTIVE --> SUSPENDED: suspend()
    
    SUSPENDED --> ACTIVE: activate()
    
    ACTIVE --> [*]: Pode fazer login
    PENDING --> [*]: Não pode fazer login
    INACTIVE --> [*]: Não pode fazer login
    SUSPENDED --> [*]: Não pode fazer login
```

---

## 9. Multi-Tenant (School-Based)

```mermaid
graph TB
    subgraph "Escola A"
        A1[Staff A] --> S1[(School A)]
        A2[Guardian A] --> S1
        A3[Students A] --> S1
    end
    
    subgraph "Escola B"
        B1[Staff B] --> S2[(School B)]
        B2[Guardian B] --> S2
        B3[Students B] --> S2
    end
    
    subgraph "Sistema"
        C[ADMIN] --> S1
        C --> S2
        C --> S3[(School C)]
    end
    
    style S1 fill:#e3f2fd
    style S2 fill:#c8e6c9
    style S3 fill:#fff3cd
```

---

## 10. Dependency Injection

```mermaid
graph TB
    A[CreateStaffUseCase] --> B[UsersRepository Interface]
    A --> C[PasswordHasher Interface]
    
    D[Symfony DI Container] --> E[DoctrineUsersRepository]
    D --> F[NativePasswordHasher]
    
    E -.implements.-> B
    F -.implements.-> C
    
    style A fill:#e3f2fd
    style D fill:#c8e6c9
```

---

## 11. Request → Response Flow

```mermaid
graph LR
    A[HTTP POST /api/users/staff] --> B[UserController]
    B --> C[Validate CreateStaffRequest]
    C --> D{Valid?}
    D -->|Yes| E[CreateStaffUseCase]
    D -->|No| F[400 Bad Request]
    
    E --> G[User Entity Created]
    G --> H[Repository Save]
    H --> I[UserResponseDTO]
    I --> J[201 Created JSON]
    
    E -.Error.-> K[DuplicateEmailException]
    K --> L[409 Conflict]
```

---

## 12. Testes (TDD Layers)

```mermaid
graph TB
    subgraph "Unit Tests"
        A1[Email VO Tests]
        A2[Phone VO Tests]
        A3[User Entity Tests]
        A4[UserRole Enum Tests]
    end
    
    subgraph "Integration Tests"
        B1[Repository Tests]
        B2[Use Case Tests]
    end
    
    subgraph "Functional Tests"
        C1[Controller Tests]
        C2[API Tests]
    end
    
    A1 --> B2
    A2 --> B2
    A3 --> B1
    B1 --> C1
    B2 --> C2
```

---

## 13. Padrões de Design Aplicados

```mermaid
mindmap
  root((Design Patterns))
    Repository Pattern
      Abstrai persistência
      Interface no domínio
      Implementação na infra
    Factory Pattern
      createStaff
      createGuardian
      createAdmin
    Value Object Pattern
      Email
      Phone
      UserId
    DTO Pattern
      CreateStaffDTO
      UserResponseDTO
      UpdateUserDTO
    Strategy Pattern
      PasswordHasher
      TokenGenerator
    Dependency Injection
      Constructor Injection
      Interface-based
```

---

## 14. Exception Handling

```mermaid
graph TB
    A[Use Case] --> B{Business Logic}
    
    B -.Email exists.-> C[DuplicateEmailException]
    B -.User not found.-> D[UserNotFoundException]
    B -.Invalid status.-> E[InvalidUserStatusException]
    B -.Invalid data.-> F[InvalidArgumentException]
    
    C --> G[Domain Exception]
    D --> G
    E --> G
    F --> G
    
    G --> H[Controller catches]
    H --> I[Transform to HTTP Response]
    I --> J[4xx/5xx with error details]
```

---

## 15. Clean Architecture Circles

```mermaid
graph TB
    subgraph "Inner Circle - Domain"
        A[Entities]
        B[Value Objects]
        C[Enums]
        D[Domain Exceptions]
        E[Repository Interfaces]
    end
    
    subgraph "Middle Circle - Application"
        F[Use Cases]
        G[DTOs]
    end
    
    subgraph "Outer Circle - Infrastructure"
        H[Doctrine Repository]
        I[Password Hasher]
        J[Controllers]
    end
    
    F --> A
    F --> E
    G --> A
    H --> E
    J --> F
    
    style A fill:#ff6b6b
    style F fill:#4ecdc4
    style H fill:#ffe66d
```

---

## Resumo Visual

### Fluxo Completo

```
HTTP Request
    ↓
Controller (Presentation)
    ↓
Use Case (Application)
    ↓
Repository Interface (Domain Contract)
    ↓
Doctrine Repository (Infrastructure)
    ↓
Database
    ↓
Response DTO
    ↓
JSON Response
```

### Camadas de Validação

```
1. Controller: Validação de entrada (Symfony Validator)
2. Value Object: Validação de formato (Email, Phone)
3. Entity: Validação de regras de negócio
4. Repository: Validação de unicidade (email exists)
```

### Separação de Responsabilidades

| Camada | Responsabilidade | Exemplo |
|--------|------------------|---------|
| Domain | Regras de negócio | `user.canLogin()` |
| Application | Orquestração | `CreateStaffUseCase` |
| Infrastructure | Implementação técnica | `DoctrineRepository` |
| Presentation | HTTP/JSON | `UserController` |

---

**Para mais detalhes**: Veja [`USERS_MODULE_ARCHITECTURE.md`](USERS_MODULE_ARCHITECTURE.md)
