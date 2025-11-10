<?php

declare(strict_types=1);

namespace App\Domain\Users\Exception;

use DomainException;

/**
 * Exceção lançada quando há tentativa de criar usuário com email duplicado
 */
final class DuplicateEmailException extends DomainException
{
    public static function withEmail(string $email): self
    {
        return new self(sprintf('Já existe um usuário cadastrado com o email "%s"', $email));
    }
}
