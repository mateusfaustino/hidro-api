<?php

declare(strict_types=1);

namespace App\Tests\Application\UseCase;

use App\Application\DTO\User\CreateSchoolAdminDTO;
use App\Application\UseCase\User\CreateSchoolAdminUseCase;
use App\Domain\Users\Exception\DuplicateEmailException;
use App\Domain\Users\UsersRepository;
use App\Domain\Users\ValueObject\Email;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Testes para CreateSchoolAdminUseCase
 */
class CreateSchoolAdminUseCaseTest extends TestCase
{
    private UsersRepository $repository;
    private UserPasswordHasherInterface $passwordHasher;
    private CreateSchoolAdminUseCase $useCase;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UsersRepository::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->useCase = new CreateSchoolAdminUseCase(
            $this->repository,
            $this->passwordHasher
        );
    }

    public function testCreateSchoolAdminSuccessfully(): void
    {
        $dto = new CreateSchoolAdminDTO(
            email: 'admin@escola.com',
            name: 'João Silva',
            password: 'senha123',
            schoolId: 'school-123',
            phone: '11987654321'
        );

        $this->repository
            ->expects($this->once())
            ->method('emailExists')
            ->with($this->callback(function (Email $email) {
                return $email->value() === 'admin@escola.com';
            }))
            ->willReturn(false);

        $this->passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->willReturn('hashed_password');

        $this->repository
            ->expects($this->once())
            ->method('save');

        $response = $this->useCase->execute($dto);

        $this->assertEquals('admin@escola.com', $response->email);
        $this->assertEquals('João Silva', $response->name);
        $this->assertContains('ROLE_SCHOOL_ADMIN', $response->roles);
    }

    public function testCreateSchoolAdminThrowsExceptionWhenEmailExists(): void
    {
        $this->expectException(DuplicateEmailException::class);

        $dto = new CreateSchoolAdminDTO(
            email: 'admin@escola.com',
            name: 'João Silva',
            password: 'senha123',
            schoolId: 'school-123'
        );

        $this->repository
            ->expects($this->once())
            ->method('emailExists')
            ->willReturn(true);

        $this->useCase->execute($dto);
    }
}
