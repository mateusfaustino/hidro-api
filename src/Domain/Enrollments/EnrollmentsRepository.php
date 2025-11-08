<?php

namespace App\Domain\Enrollments;

interface EnrollmentsRepository
{
    public function findById(string $id): ?Enrollment;
    public function save(Enrollment $enrollment): void;
    public function delete(Enrollment $enrollment): void;
}