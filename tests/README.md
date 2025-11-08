# Testing Structure

## Approach

This project follows the Test-Driven Development (TDD) approach with the Red-Green-Refactor cycle:

1. **Red**: Write a failing test first
2. **Green**: Write minimal code to make the test pass
3. **Refactor**: Improve the code while keeping tests passing

## Directory Structure

```
tests/
├── Domain/
│   └── Contains tests for domain entities and value objects
├── Application/
│   ├── Service/  - Tests for application services
│   └── UseCase/  - Tests for use cases/interactors
└── Infrastructure/
    └── Tests for infrastructure implementations
```

## Test Types

1. **Unit Tests**: Focus on individual classes and methods
2. **Integration Tests**: Test interactions between components
3. **Functional Tests**: Test complete features/endpoints

## Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test
./vendor/bin/phpunit tests/Domain/Fees/FeeTest.php

# Run tests with coverage
./vendor/bin/phpunit --coverage-html coverage
```

## Test Conventions

- Test class names should match the class being tested with "Test" suffix
- Test method names should be descriptive and start with "test"
- Use appropriate assertions for the expected outcomes
- Mock external dependencies when testing units in isolation