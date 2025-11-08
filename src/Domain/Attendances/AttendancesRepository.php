<?php

namespace App\Domain\Attendances;

interface AttendancesRepository
{
    public function findById(string $id): ?Attendance;
    public function save(Attendance $attendance): void;
    public function delete(Attendance $attendance): void;
}