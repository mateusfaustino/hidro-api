<?php

declare(strict_types=1);

namespace App\Tests\Domain\Users\ValueObject;

use App\Domain\Users\ValueObject\Phone;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Testes para Value Object Phone
 */
class PhoneTest extends TestCase
{
    public function testCreateValidPhone(): void
    {
        $phone = Phone::fromString('11987654321');
        
        $this->assertEquals('11987654321', $phone->value());
    }

    public function testPhoneIsNormalized(): void
    {
        $phone = Phone::fromString('(11) 98765-4321');
        
        $this->assertEquals('11987654321', $phone->value());
    }

    public function testPhoneFormatted(): void
    {
        $phone = Phone::fromString('11987654321');
        
        $this->assertEquals('(11) 98765-4321', $phone->formatted());
    }

    public function testEmptyPhoneThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Telefone não pode ser vazio');
        
        Phone::fromString('');
    }

    public function testInvalidPhoneThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Telefone inválido');
        
        Phone::fromString('123');
    }
}
