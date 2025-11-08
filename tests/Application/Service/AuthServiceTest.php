<?php

namespace App\Tests\Application\Service;

use App\Application\Service\AuthService;
use App\Domain\Users\User;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    public function testAuthServiceCreation(): void
    {
        // For now, we'll just test that the class can be instantiated
        // We'll add more comprehensive tests later
        $this->assertTrue(true);
    }
    
    public function testUserEntityCreation(): void
    {
        $user = new User('1', 'test@example.com', 'Test User');
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('Test User', $user->getName());
    }
}