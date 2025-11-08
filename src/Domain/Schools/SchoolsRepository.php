<?php

namespace App\Domain\Schools;

interface SchoolsRepository
{
    public function findById(string $id): ?School;
    public function save(School $school): void;
    public function delete(School $school): void;
}