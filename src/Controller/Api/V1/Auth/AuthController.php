<?php

namespace App\Controller\Api\V1\Auth;

use App\Application\DTO\LoginRequestDTO;
use App\Application\DTO\RefreshTokenRequestDTO;
use App\Application\UseCase\AuthenticateUserUseCase;
use App\Application\UseCase\LogoutUserUseCase;
use App\Application\UseCase\RefreshAccessTokenUseCase;
use App\Domain\Auth\Exception\InvalidCredentialsException;
use App\Domain\Auth\Exception\InvalidRefreshTokenException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route('/api/v1/auth', name: 'auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthenticateUserUseCase $authenticateUser,
        private readonly RefreshAccessTokenUseCase $refreshAccessToken,
        private readonly LogoutUserUseCase $logoutUser,
    ) {
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = $this->decodeRequest($request);

        if (!isset($data['email'], $data['password']) || $data['email'] === '' || $data['password'] === '') {
            return $this->problem('invalid_payload', 'Invalid payload provided.', 400);
        }

        $loginRequest = LoginRequestDTO::fromArray($data);

        try {
            $tokenPair = $this->authenticateUser->execute(
                $loginRequest,
                $request->getClientIp(),
                $request->headers->get('User-Agent')
            );
        } catch (InvalidCredentialsException $exception) {
            return $this->problem('invalid_credentials', $exception->getMessage(), 401);
        } catch (Throwable $exception) {
            return $this->problem('authentication_failed', 'Unable to authenticate user.', 500);
        }

        return new JsonResponse($tokenPair->toArray(), 200);
    }

    #[Route('/token/refresh', name: 'refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        $data = $this->decodeRequest($request);

        if (!isset($data['refresh_token']) || $data['refresh_token'] === '') {
            return $this->problem('invalid_payload', 'Refresh token is required.', 400);
        }

        try {
            $tokenPair = $this->refreshAccessToken->execute(
                RefreshTokenRequestDTO::fromArray($data),
                $request->getClientIp(),
                $request->headers->get('User-Agent')
            );
        } catch (InvalidRefreshTokenException $exception) {
            return $this->problem('invalid_refresh_token', $exception->getMessage(), 401);
        } catch (Throwable $exception) {
            return $this->problem('refresh_failed', 'Unable to refresh access token.', 500);
        }

        return new JsonResponse($tokenPair->toArray(), 200);
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        $data = $this->decodeRequest($request);

        if (!isset($data['refresh_token']) || $data['refresh_token'] === '') {
            return $this->problem('invalid_payload', 'Refresh token is required.', 400);
        }

        $this->logoutUser->execute(
            RefreshTokenRequestDTO::fromArray($data),
            $request->getClientIp()
        );

        return new JsonResponse(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeRequest(Request $request): array
    {
        $content = $request->getContent();

        if ($content === '') {
            return [];
        }

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return [];
        }

        if (!is_array($data)) {
            return [];
        }

        return $data;
    }

    private function problem(string $type, string $detail, int $status): JsonResponse
    {
        return new JsonResponse([
            'type' => $type,
            'title' => $this->titleFromType($type),
            'status' => $status,
            'detail' => $detail,
        ], $status);
    }

    private function titleFromType(string $type): string
    {
        return match ($type) {
            'invalid_credentials' => 'Invalid credentials provided',
            'invalid_refresh_token' => 'Refresh token invalid',
            'invalid_payload' => 'Invalid request payload',
            default => 'Authentication error',
        };
    }
}
