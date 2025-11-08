<?php

namespace App\Domain\Guardians;

class Guardian
{
    private string $id;
    private string $name;
    private string $phone;
    
    public function __construct(string $id, string $name, string $phone)
    {
        $this->id = $id;
        $this->name = $name;
        $this->phone = $phone;
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getPhone(): string
    {
        return $this->phone;
    }
}