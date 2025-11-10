<?php

declare(strict_types=1);

namespace App\Application\UseCase\User;

use App\Domain\Users\Exception\UserNotFoundException;
use App\Domain\Users\UsersRepository;
use App\Domain\Users\ValueObject\UserId;

/**
 * Use Case: Remover Usuário
 * 
 * Soft delete - marca como inativo mas mantém dados
 */
final class DeleteUserUseCase
{
    public function __construct(
        private readonly UsersRepository $usersRepository
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    public function execute(string $userId): void
    {
        $id = UserId::fromString($userId);
        $user = $this->usersRepository->findById($id);

        if ($user === null) {
            throw UserNotFoundException::withId($userId);
        }

        // Soft delete - desativa o usuário
        $user->deactivate();
        $this->usersRepository->save($user);
    }
}
