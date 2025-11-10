<?php

declare(strict_types=1);

namespace App\Domain\Users\ValueObject;

use InvalidArgumentException;

/**
 * Value Object para Telefone
 * 
 * Garante que o telefone seja válido
 */
final class Phone
{
    private string $value;

    private function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $this->normalize($value);
    }

    public static function fromString(string $phone): self
    {
        return new self($phone);
    }

    private function validate(string $phone): void
    {
        if (empty($phone)) {
            throw new InvalidArgumentException('Telefone não pode ser vazio');
        }

        // Remove caracteres não numéricos para validação
        $numbers = preg_replace('/\D/', '', $phone);

        // Aceita:
        // - 10-11 dígitos (formato nacional brasileiro)
        // - 12-13 dígitos (formato internacional com código do país +55)
        $length = strlen($numbers);
        
        if ($length < 10 || $length > 13) {
            throw new InvalidArgumentException('Telefone inválido. Use formato: (XX) XXXXX-XXXX ou +55 (XX) XXXXX-XXXX');
        }
        
        // Se tem 12-13 dígitos, deve começar com 55 (código do Brasil)
        if ($length > 11 && !str_starts_with($numbers, '55')) {
            throw new InvalidArgumentException('Telefone internacional deve usar código do Brasil (+55)');
        }
    }

    private function normalize(string $phone): string
    {
        // Remove todos os caracteres não numéricos
        $numbers = preg_replace('/\D/', '', $phone);
        
        // Se tem código do país (+55), remove para armazenar apenas número nacional
        if (strlen($numbers) > 11 && str_starts_with($numbers, '55')) {
            return substr($numbers, 2); // Remove o código 55
        }
        
        return $numbers;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function formatted(): string
    {
        // Formata: (XX) XXXXX-XXXX ou (XX) XXXX-XXXX
        $length = strlen($this->value);
        
        if ($length === 11) {
            return sprintf(
                '(%s) %s-%s',
                substr($this->value, 0, 2),
                substr($this->value, 2, 5),
                substr($this->value, 7)
            );
        }
        
        return sprintf(
            '(%s) %s-%s',
            substr($this->value, 0, 2),
            substr($this->value, 2, 4),
            substr($this->value, 6)
        );
    }

    public function equals(Phone $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->formatted();
    }
}
