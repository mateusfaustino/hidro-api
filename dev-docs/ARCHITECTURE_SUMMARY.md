# Clean Architecture / DDD Structure Implementation

## Overview

This document summarizes the implementation of the Clean Architecture/DDD structure for the Hidro API project.

## Directory Structure Created

### Domain Layer
```
src/Domain/
├── Common/
│   └── AggregateRoot.php
├── Schools/
│   ├── School.php
│   └── SchoolsRepository.php
├── Users/
│   ├── User.php
│   └── UsersRepository.php
├── Students/
│   ├── Student.php
│   └── StudentsRepository.php
├── Guardians/
│   ├── Guardian.php
│   └── GuardiansRepository.php
├── Classes/
│   ├── ClassEntity.php
│   └── ClassesRepository.php
├── Enrollments/
│   ├── Enrollment.php
│   └── EnrollmentsRepository.php
├── Fees/
│   ├── Fee.php
│   └── FeesRepository.php
├── Payments/
│   ├── Payment.php
│   └── PaymentsRepository.php
├── Attendances/
│   ├── Attendance.php
│   └── AttendancesRepository.php
└── Evolutions/
    ├── Evolution.php
    └── EvolutionsRepository.php
```

### Application Layer
```
src/Application/
├── UseCase/
│   ├── UseCaseInterface.php
│   └── CreateFeeUseCase.php
├── DTO/
│   └── FeeDTO.php
└── Service/
    └── FeeService.php
```

### Infrastructure Layer
```
src/Infrastructure/
├── Persistence/
│   └── Doctrine/
│       └── DoctrineFeesRepository.php
├── Mysql/
├── Mongo/
├── Security/
├── Bus/
├── Queue/
└── Telemetry/
```

### Presentation Layer
```
src/Presentation/
└── Http/
    ├── Controller/
    │   └── FeeController.php
    ├── Request/
    │   └── CreateFeeRequest.php
    ├── Response/
    │   └── FeeResponse.php
    └── Security/
```

### Tests
```
tests/
├── Domain/
│   └── Fees/
│       └── FeeTest.php
└── Application/
    ├── Service/
    │   └── FeeServiceTest.php
    └── UseCase/
        └── CreateFeeUseCaseTest.php
```

## Key Components Implemented

### 1. Domain Entities
- `Fee` entity with basic properties and getters
- `School` entity with basic properties and getters
- `User` entity with basic properties and getters
- `Student` entity with basic properties and getters
- `ClassEntity` with basic properties and getters

### 2. Repository Interfaces
- `FeesRepository` interface with standard CRUD operations
- `SchoolsRepository` interface with standard CRUD operations
- `UsersRepository` interface with standard CRUD operations
- `StudentsRepository` interface with standard CRUD operations
- `ClassesRepository` interface with standard CRUD operations

### 3. Application Services
- `FeeService` with methods for creating and retrieving fees

### 4. Use Cases
- `UseCaseInterface` as a contract for all use cases
- `CreateFeeUseCase` implementing the use case interface

### 5. DTOs
- `FeeDTO` for data transfer between layers
- `CreateFeeRequest` for handling HTTP request data
- `FeeResponse` for formatting HTTP response data

### 6. Controllers
- `FeeController` with basic routing annotations

### 7. Infrastructure
- `DoctrineFeesRepository` as a placeholder implementation

## Testing Structure

Basic test files have been created following the TDD approach:
1. Created failing tests (Red phase)
2. Implemented minimal classes to make tests pass (Green phase)
3. Structure is ready for refactoring (Refactor phase)

## Next Steps

1. Implement proper PHPUnit test configuration
2. Complete the repository implementations
3. Add more comprehensive tests
4. Implement the remaining domain entities and repositories
5. Add business logic to the use cases
6. Complete the controller implementations
7. Set up proper dependency injection
8. Configure routing and serialization

## Notes

- All classes follow PSR-12 coding standards
- Namespaces are properly organized according to the directory structure
- Interfaces are used to define contracts between layers
- Dependency injection is used to maintain loose coupling
- The structure supports the multi-tenant architecture with school_id in entities