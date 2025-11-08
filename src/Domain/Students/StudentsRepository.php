<?php

namespace App\Domain\Students;

interface StudentsRepository
{
    public function findById(string $id): ?Student;
    public function save(Student $student): void;
    public function delete(Student $student): void;
}