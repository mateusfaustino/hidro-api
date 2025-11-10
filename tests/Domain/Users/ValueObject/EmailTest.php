<?php

declare(strict_types=1);

namespace App\Tests\Domain\Users\ValueObject;

use App\Domain\Users\ValueObject\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Testes para Value Object Email
 */
class EmailTest extends TestCase
{
    public function testCreateValidEmail(): void
    {
        $email = Email::fromString('teste@example.com');
        
        $this->assertEquals('teste@example.com', $email->value());
    }

    public function testEmailIsNormalized(): void
    {
        $email = Email::fromString('  TESTE@EXAMPLE.COM  ');
        
        $this->assertEquals('teste@example.com', $email->value());
    }

    public function testEmptyEmailThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email não pode ser vazio');
        
        Email::fromString('');
    }

    public function testInvalidEmailThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email inválido');
        
        Email::fromString('invalid-email');
    }

    public function testEmailTooLongThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email muito longo');
        
        $longEmail = str_repeat('a', 250) . '@example.com';
        Email::fromString($longEmail);
    }

    public function testEmailEquality(): void
    {
        $email1 = Email::fromString('teste@example.com');
        $email2 = Email::fromString('teste@example.com');
        $email3 = Email::fromString('outro@example.com');
        
        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
    }

    public function testEmailToString(): void
    {
        $email = Email::fromString('teste@example.com');
        
        $this->assertEquals('teste@example.com', (string) $email);
    }
}
