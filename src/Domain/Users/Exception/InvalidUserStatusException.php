<?php

declare(strict_types=1);

namespace App\Domain\Users\Exception;

use DomainException;

/**
 * Exceção lançada quando status do usuário é inválido para operação
 */
final class InvalidUserStatusException extends DomainException
{
    public static function cannotLogin(string $status): self
    {
        return new self(sprintf('Usuário com status "%s" não pode fazer login', $status));
    }

    public static function cannotPerformAction(string $action, string $status): self
    {
        return new self(sprintf('Usuário com status "%s" não pode executar a ação "%s"', $status, $action));
    }
}
