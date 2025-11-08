<?php

namespace App\Domain\Evolutions;

interface EvolutionsRepository
{
    public function findById(string $id): ?Evolution;
    public function save(Evolution $evolution): void;
    public function delete(Evolution $evolution): void;
}