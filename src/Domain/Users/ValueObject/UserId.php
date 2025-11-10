<?php

declare(strict_types=1);

namespace App\Domain\Users\ValueObject;

use InvalidArgumentException;

/**
 * Value Object para ID de Usuário
 * 
 * Garante que o ID seja sempre um UUID válido
 */
final class UserId
{
    private string $value;

    private function __construct(string $id)
    {
        $this->validate($id);
        $this->value = $id;
    }

    public static function generate(): self
    {
        return new self(self::generateUuid());
    }

    public static function fromString(string $id): self
    {
        return new self($id);
    }

    private function validate(string $id): void
    {
        // Valida formato UUID v4
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        
        if (!preg_match($pattern, $id)) {
            throw new InvalidArgumentException('ID de usuário inválido. Deve ser um UUID v4 válido.');
        }
    }

    private static function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(UserId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
