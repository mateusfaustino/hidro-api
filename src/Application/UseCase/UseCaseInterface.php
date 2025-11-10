<?php

declare(strict_types=1);

namespace App\Application\UseCase;

/**
 * Base interface for all Use Cases
 * 
 * Use Cases implement the application layer business logic.
 * They orchestrate domain entities and infrastructure services.
 * 
 * Note: Concrete implementations should use specific type hints
 * for their execute() method to maintain type safety.
 */
interface UseCaseInterface
{
    /**
     * Execute the use case
     * 
     * @param mixed $request Input DTO or parameters
     * @return mixed Output DTO or result
     */
    public function execute(mixed $request = null): mixed;
}