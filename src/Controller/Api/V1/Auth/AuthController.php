<?php

namespace App\Controller\Api\V1\Auth;

use App\Application\DTO\LoginRequest;
use App\Application\Service\AuthService;
use App\Domain\Users\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/api/v1/auth', name: 'auth_')]
class AuthController extends AbstractController
{
    private UsersRepository $usersRepository;
    private AuthService $authService;
    private UserPasswordHasherInterface $passwordHasher;
    
    public function __construct(
        UsersRepository $usersRepository,
        AuthService $authService,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->usersRepository = $usersRepository;
        $this->authService = $authService;
        $this->passwordHasher = $passwordHasher;
    }
    
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $loginRequest = LoginRequest::fromArray($data);
            
            // Find user by email
            $user = $this->usersRepository->findByEmail($loginRequest->email);
            
            if (!$user) {
                return new JsonResponse([
                    'error' => 'Invalid credentials'
                ], 401);
            }
            
            // Verify password
            if (!$this->passwordHasher->isPasswordValid($user, $loginRequest->password)) {
                return new JsonResponse([
                    'error' => 'Invalid credentials'
                ], 401);
            }
            
            // Create tokens
            $tokens = $this->authService->createTokens($user);
            
            return new JsonResponse([
                'token' => $tokens['token'],
                'refresh_token' => $tokens['refresh_token'],
                'expires_in' => $tokens['expires_in']
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Authentication failed'
            ], 401);
        }
    }
    
    #[Route('/refresh', name: 'refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $refreshToken = $data['refresh_token'] ?? '';
            
            if (empty($refreshToken)) {
                return new JsonResponse([
                    'error' => 'Refresh token is required'
                ], 400);
            }
            
            $tokens = $this->authService->refreshToken($refreshToken);
            
            if (!$tokens) {
                return new JsonResponse([
                    'error' => 'Invalid or expired refresh token'
                ], 401);
            }
            
            return new JsonResponse([
                'token' => $tokens['token'],
                'refresh_token' => $tokens['refresh_token'],
                'expires_in' => $tokens['expires_in']
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Token refresh failed'
            ], 401);
        }
    }
    
    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $refreshToken = $data['refresh_token'] ?? '';
            
            if (empty($refreshToken)) {
                return new JsonResponse([
                    'error' => 'Refresh token is required'
                ], 400);
            }
            
            $result = $this->authService->logout($refreshToken);
            
            if (!$result) {
                return new JsonResponse([
                    'error' => 'Invalid refresh token'
                ], 401);
            }
            
            return new JsonResponse([
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Logout failed'
            ], 500);
        }
    }
}