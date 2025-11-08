<?php

namespace App\Domain\Enrollments;

class Enrollment
{
    private string $id;
    private string $studentId;
    private string $classId;
    private string $enrollmentDate;
    
    public function __construct(string $id, string $studentId, string $classId, string $enrollmentDate)
    {
        $this->id = $id;
        $this->studentId = $studentId;
        $this->classId = $classId;
        $this->enrollmentDate = $enrollmentDate;
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
    
    public function getEnrollmentDate(): string
    {
        return $this->enrollmentDate;
    }
}