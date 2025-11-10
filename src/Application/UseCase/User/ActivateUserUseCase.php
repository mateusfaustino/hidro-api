<?php

declare(strict_types=1);

namespace App\Application\UseCase\User;

use App\Application\DTO\User\UserResponseDTO;
use App\Domain\Users\Exception\UserNotFoundException;
use App\Domain\Users\UsersRepository;
use App\Domain\Users\ValueObject\UserId;

/**
 * Use Case: Ativar Usuário
 * 
 * Permite que um responsável (PENDING) seja ativado por um Staff/Admin
 */
final class ActivateUserUseCase
{
    public function __construct(
        private readonly UsersRepository $usersRepository
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    public function execute(string $userId): UserResponseDTO
    {
        $id = UserId::fromString($userId);
        $user = $this->usersRepository->findById($id);

        if (!$user) {
            throw UserNotFoundException::withId($userId);
        }

        $user->activate();
        $this->usersRepository->save($user);

        return UserResponseDTO::fromEntity($user);
    }
}
