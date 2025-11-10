<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Application\DTO\User\CreateGuardianDTO;
use App\Application\DTO\User\CreateSchoolAdminDTO;
use App\Application\DTO\User\CreateSecretaryDTO;
use App\Application\DTO\User\CreateTeacherDTO;
use App\Application\DTO\User\ListUsersDTO;
use App\Application\DTO\User\UpdateUserDTO;
use App\Application\UseCase\User\ActivateUserUseCase;
use App\Application\UseCase\User\CreateGuardianUseCase;
use App\Application\UseCase\User\CreateSchoolAdminUseCase;
use App\Application\UseCase\User\CreateSecretaryUseCase;
use App\Application\UseCase\User\CreateTeacherUseCase;
use App\Application\UseCase\User\DeactivateUserUseCase;
use App\Application\UseCase\User\DeleteUserUseCase;
use App\Application\UseCase\User\GetUserByIdUseCase;
use App\Application\UseCase\User\ListUsersBySchoolUseCase;
use App\Application\UseCase\User\ListUsersPaginatedUseCase;
use App\Application\UseCase\User\SuspendUserUseCase;
use App\Application\UseCase\User\UpdateUserUseCase;
use App\Domain\Users\Exception\DuplicateEmailException;
use App\Domain\Users\Exception\UserNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Controller para gerenciamento de usuários
 * 
 * Implementa CRUD completo com paginação, filtros, ordenação e busca
 * Controle de acesso baseado em roles (RBAC) e multi-tenant (X-School-Id)
 * 
 * Endpoints disponíveis:
 * - POST   /api/v1/users/school-admin  - Criar administrador da escola
 * - POST   /api/v1/users/secretary     - Criar secretária
 * - POST   /api/v1/users/teacher       - Criar professor
 * - POST   /api/v1/users/guardian      - Criar responsável (auto-cadastro)
 * - GET    /api/v1/users               - Listar usuários (paginado, com filtros)
 * - GET    /api/v1/users/{id}          - Buscar usuário por ID
 * - PATCH  /api/v1/users/{id}          - Atualizar usuário
 * - DELETE /api/v1/users/{id}          - Remover usuário (soft delete)
 * - POST   /api/v1/users/{id}/activate    - Ativar usuário
 * - POST   /api/v1/users/{id}/deactivate - Desativar usuário
 * - POST   /api/v1/users/{id}/suspend    - Suspender usuário
 * 
 * Parâmetros de lista paginada (GET /api/v1/users):
 * - page: Número da página (padrão: 1)
 * - per_page: Itens por página (padrão: 20, máx: 100)
 * - sort_by: Campo de ordenação (name, email, created_at, status)
 * - sort_order: Ordem (ASC, DESC)
 * - role: Filtrar por role (ROLE_SCHOOL_ADMIN, ROLE_SECRETARY, etc.)
 * - status: Filtrar por status (active, inactive, suspended, pending)
 * - search: Buscar por nome ou email
 * 
 * Todas as requisições autenticadas devem incluir:
 * - Header: X-School-Id (obrigatório para multi-tenant)
 * - Header: Authorization: Bearer {token}
 */
#[Route('/api/v1/users', name: 'api_users_')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly ValidatorInterface $validator
    ) {
    }

    /**
     * Criar Administrador da Escola
     * 
     * @route POST /api/v1/users/school-admin
     */
    #[Route('/school-admin', name: 'create_school_admin', methods: ['POST'])]
    public function createSchoolAdmin(
        Request $request,
        CreateSchoolAdminUseCase $useCase
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);
            
            // Extrai school_id do header (multi-tenant)
            $schoolId = $request->headers->get('X-School-Id');
            if (!$schoolId) {
                return $this->json([
                    'error' => 'X-School-Id header é obrigatório'
                ], Response::HTTP_BAD_REQUEST);
            }

            $dto = new CreateSchoolAdminDTO(
                email: $data['email'] ?? '',
                name: $data['name'] ?? '',
                password: $data['password'] ?? '',
                schoolId: $schoolId,
                phone: $data['phone'] ?? null
            );

            $response = $useCase->execute($dto);

            return $this->json($response->toArray(), Response::HTTP_CREATED);

        } catch (DuplicateEmailException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_CONFLICT);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erro ao criar administrador da escola'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Criar Secretária
     * 
     * @route POST /api/v1/users/secretary
     */
    #[Route('/secretary', name: 'create_secretary', methods: ['POST'])]
    public function createSecretary(
        Request $request,
        CreateSecretaryUseCase $useCase
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);
            $schoolId = $request->headers->get('X-School-Id');
            
            if (!$schoolId) {
                return $this->json([
                    'error' => 'X-School-Id header é obrigatório'
                ], Response::HTTP_BAD_REQUEST);
            }

            $dto = new CreateSecretaryDTO(
                email: $data['email'] ?? '',
                name: $data['name'] ?? '',
                password: $data['password'] ?? '',
                schoolId: $schoolId,
                phone: $data['phone'] ?? null
            );

            $response = $useCase->execute($dto);

            return $this->json($response->toArray(), Response::HTTP_CREATED);

        } catch (DuplicateEmailException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_CONFLICT);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erro ao criar secretária'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Criar Professor
     * 
     * @route POST /api/v1/users/teacher
     */
    #[Route('/teacher', name: 'create_teacher', methods: ['POST'])]
    public function createTeacher(
        Request $request,
        CreateTeacherUseCase $useCase
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);
            $schoolId = $request->headers->get('X-School-Id');
            
            if (!$schoolId) {
                return $this->json([
                    'error' => 'X-School-Id header é obrigatório'
                ], Response::HTTP_BAD_REQUEST);
            }

            $dto = new CreateTeacherDTO(
                email: $data['email'] ?? '',
                name: $data['name'] ?? '',
                password: $data['password'] ?? '',
                schoolId: $schoolId,
                phone: $data['phone'] ?? null
            );

            $response = $useCase->execute($dto);

            return $this->json($response->toArray(), Response::HTTP_CREATED);

        } catch (DuplicateEmailException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_CONFLICT);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erro ao criar professor'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Criar Responsável (auto-cadastro)
     * 
     * @route POST /api/v1/users/guardian
     */
    #[Route('/guardian', name: 'create_guardian', methods: ['POST'])]
    public function createGuardian(
        Request $request,
        CreateGuardianUseCase $useCase
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);
            $schoolId = $request->headers->get('X-School-Id');
            
            if (!$schoolId) {
                return $this->json([
                    'error' => 'X-School-Id header é obrigatório'
                ], Response::HTTP_BAD_REQUEST);
            }

            $dto = new CreateGuardianDTO(
                email: $data['email'] ?? '',
                phone: $data['phone'] ?? '',
                name: $data['name'] ?? '',
                password: $data['password'] ?? '',
                schoolId: $schoolId
            );

            $response = $useCase->execute($dto);

            return $this->json(
                array_merge($response->toArray(), [
                    'message' => 'Responsável criado com sucesso. Aguarde aprovação da escola.'
                ]),
                Response::HTTP_CREATED
            );

        } catch (DuplicateEmailException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_CONFLICT);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erro ao criar responsável'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Buscar usuário por ID
     * 
     * @route GET /api/v1/users/{id}
     */
    #[Route('/{id}', name: 'get_by_id', methods: ['GET'])]
    public function getUserById(
        string $id,
        GetUserByIdUseCase $useCase
    ): JsonResponse {
        try {
            $response = $useCase->execute($id);
            return $this->json($response->toArray());

        } catch (UserNotFoundException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erro ao buscar usuário'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Listar usuários da escola com paginação, filtros e busca
     * 
     * @route GET /api/v1/users?page=1&per_page=20&sort_by=created_at&sort_order=DESC&role=ROLE_TEACHER&status=active&search=john
     */
    #[Route('', name: 'list_by_school', methods: ['GET'])]
    public function listUsersBySchool(
        Request $request,
        ListUsersPaginatedUseCase $useCase
    ): JsonResponse {
        try {
            $schoolId = $request->headers->get('X-School-Id');
            
            if (!$schoolId) {
                return $this->json([
                    'error' => 'X-School-Id header é obrigatório'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Parâmetros de paginação e filtros
            $dto = new ListUsersDTO(
                schoolId: $schoolId,
                page: (int) ($request->query->get('page', 1)),
                perPage: (int) ($request->query->get('per_page', 20)),
                sortBy: $request->query->get('sort_by', 'created_at'),
                sortOrder: strtoupper($request->query->get('sort_order', 'DESC')),
                role: $request->query->get('role'),
                status: $request->query->get('status'),
                search: $request->query->get('search')
            );

            $response = $useCase->execute($dto);

            return $this->json($response->toArray());

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erro ao listar usuários'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Atualizar usuário
     * 
     * @route PATCH /api/v1/users/{id}
     */
    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function updateUser(
        string $id,
        Request $request,
        UpdateUserUseCase $useCase
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            $dto = new UpdateUserDTO(
                userId: $id,
                name: $data['name'] ?? null,
                phone: $data['phone'] ?? null,
                email: $data['email'] ?? null
            );

            $response = $useCase->execute($dto);

            return $this->json($response->toArray());

        } catch (UserNotFoundException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erro ao atualizar usuário'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Ativar responsável pendente
     * 
     * @route POST /api/v1/users/{id}/activate
     */
    #[Route('/{id}/activate', name: 'activate', methods: ['POST'])]
    public function activateUser(
        string $id,
        ActivateUserUseCase $useCase
    ): JsonResponse {
        try {
            $response = $useCase->execute($id);

            return $this->json([
                'message' => 'Usuário ativado com sucesso',
                'data' => $response->toArray()
            ]);

        } catch (UserNotFoundException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erro ao ativar usuário'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Desativar usuário
     * 
     * @route POST /api/v1/users/{id}/deactivate
     */
    #[Route('/{id}/deactivate', name: 'deactivate', methods: ['POST'])]
    public function deactivateUser(
        string $id,
        DeactivateUserUseCase $useCase
    ): JsonResponse {
        try {
            $response = $useCase->execute($id);

            return $this->json([
                'message' => 'Usuário desativado com sucesso',
                'data' => $response->toArray()
            ]);

        } catch (UserNotFoundException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erro ao desativar usuário'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Suspender usuário
     * 
     * @route POST /api/v1/users/{id}/suspend
     */
    #[Route('/{id}/suspend', name: 'suspend', methods: ['POST'])]
    public function suspendUser(
        string $id,
        SuspendUserUseCase $useCase
    ): JsonResponse {
        try {
            $response = $useCase->execute($id);

            return $this->json([
                'message' => 'Usuário suspenso com sucesso',
                'data' => $response->toArray()
            ]);

        } catch (UserNotFoundException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erro ao suspender usuário'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remover usuário (soft delete)
     * 
     * @route DELETE /api/v1/users/{id}
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function deleteUser(
        string $id,
        DeleteUserUseCase $useCase
    ): JsonResponse {
        try {
            $useCase->execute($id);

            return $this->json([
                'message' => 'Usuário removido com sucesso'
            ], Response::HTTP_NO_CONTENT);

        } catch (UserNotFoundException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erro ao remover usuário'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
