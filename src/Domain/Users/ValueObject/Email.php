<?php

declare(strict_types=1);

namespace App\Domain\Users\ValueObject;

use InvalidArgumentException;

/**
 * Value Object para Email
 * 
 * Garante que o email seja sempre válido
 */
final class Email
{
    private string $value;

    private function __construct(string $value)
    {
        $this->validate($value);
        $this->value = strtolower(trim($value));
    }

    public static function fromString(string $email): self
    {
        return new self($email);
    }

    private function validate(string $email): void
    {
        if (empty($email)) {
            throw new InvalidArgumentException('Email não pode ser vazio');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email inválido: ' . $email);
        }

        // Verificar comprimento máximo
        if (strlen($email) > 255) {
            throw new InvalidArgumentException('Email muito longo');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
