<?php

declare(strict_types=1);

namespace App\Domain\Users\Exception;

use DomainException;

/**
 * Exceção lançada quando um usuário não é encontrado
 */
final class UserNotFoundException extends DomainException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('Usuário com ID "%s" não foi encontrado', $id));
    }

    public static function withEmail(string $email): self
    {
        return new self(sprintf('Usuário com email "%s" não foi encontrado', $email));
    }
}
