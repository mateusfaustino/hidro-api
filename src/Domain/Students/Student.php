<?php

namespace App\Domain\Students;

class Student
{
    private string $id;
    private string $name;
    private string $birthDate;
    
    public function __construct(string $id, string $name, string $birthDate)
    {
        $this->id = $id;
        $this->name = $name;
        $this->birthDate = $birthDate;
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getBirthDate(): string
    {
        return $this->birthDate;
    }
}