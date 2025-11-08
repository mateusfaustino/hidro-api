<?php

namespace App\Domain\Fees;

interface FeesRepository
{
    public function findById(string $id): ?Fee;
    public function save(Fee $fee): void;
    public function delete(Fee $fee): void;
}