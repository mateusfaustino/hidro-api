<?php

namespace App\Domain\Guardians;

interface GuardiansRepository
{
    public function findById(string $id): ?Guardian;
    public function save(Guardian $guardian): void;
    public function delete(Guardian $guardian): void;
}