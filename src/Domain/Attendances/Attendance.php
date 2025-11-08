<?php

namespace App\Domain\Attendances;

class Attendance
{
    private string $id;
    private string $studentId;
    private string $classId;
    private string $date;
    private bool $present;
    
    public function __construct(string $id, string $studentId, string $classId, string $date, bool $present)
    {
        $this->id = $id;
        $this->studentId = $studentId;
        $this->classId = $classId;
        $this->date = $date;
        $this->present = $present;
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getStudentId(): string
    {
        return $this->studentId;
    }
    
    public function getClassId(): string
    {
        return $this->classId;
    }
    
    public function getDate(): string
    {
        return $this->date;
    }
    
    public function isPresent(): bool
    {
        return $this->present;
    }
}