<?php

declare(strict_types=1);

namespace App\Application\UseCase\User;

use App\Application\DTO\User\UserResponseDTO;
use App\Domain\Users\Exception\UserNotFoundException;
use App\Domain\Users\UsersRepository;
use App\Domain\Users\ValueObject\UserId;

/**
 * Use Case: Buscar Usuário por ID
 */
final class GetUserByIdUseCase
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

        return UserResponseDTO::fromEntity($user);
    }
}
