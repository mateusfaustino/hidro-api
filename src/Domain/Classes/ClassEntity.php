<?php

namespace App\Domain\Classes;

class ClassEntity
{
    private string $id;
    private string $name;
    private string $schedule;
    
    public function __construct(string $id, string $name, string $schedule)
    {
        $this->id = $id;
        $this->name = $name;
        $this->schedule = $schedule;
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getSchedule(): string
    {
        return $this->schedule;
    }
}