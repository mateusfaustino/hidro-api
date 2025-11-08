<?php

namespace App\Domain\Classes;

interface ClassesRepository
{
    public function findById(string $id): ?ClassEntity;
    public function save(ClassEntity $class): void;
    public function delete(ClassEntity $class): void;
}