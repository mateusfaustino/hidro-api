<?php

namespace App\Domain\Evolutions;

class Evolution
{
    private string $id;
    private string $studentId;
    private string $description;
    private string $date;
    
    public function __construct(string $id, string $studentId, string $description, string $date)
    {
        $this->id = $id;
        $this->studentId = $studentId;
        $this->description = $description;
        $this->date = $date;
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getStudentId(): string
    {
        return $this->studentId;
    }
    
    public function getDescription(): string
    {
        return $this->description;
    }
    
    public function getDate(): string
    {
        return $this->date;
    }
}