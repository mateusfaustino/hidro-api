<?php

namespace App\Application\Service;

use App\Domain\Users\User;
use App\Domain\Users\UsersRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;

class AuthService
{
    private JWTTokenManagerInterface $jwtManager;
    private RefreshTokenManagerInterface $refreshTokenManager;
    private UsersRepository $usersRepository;
    
    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        RefreshTokenManagerInterface $refreshTokenManager,
        UsersRepository $usersRepository
    ) {
        $this->jwtManager = $jwtManager;
        $this->refreshTokenManager = $refreshTokenManager;
        $this->usersRepository = $usersRepository;
    }
    
    public function createTokens(User $user): array
    {
        // Generate JWT token
        $token = $this->jwtManager->create($user);
        
        // Generate refresh token
        $refreshToken = $this->refreshTokenManager->create();
        $refreshToken->setUsername($user->getUserIdentifier());
        $refreshToken->setRefreshToken();
        $refreshToken->setValid(new \DateTimeImmutable('+7 days'));
        
        $this->refreshTokenManager->save($refreshToken);
        
        return [
            'token' => $token,
            'refresh_token' => $refreshToken->getRefreshToken(),
            'expires_in' => 600 // 10 minutes
        ];
    }
    
    public function refreshToken(string $refreshTokenString): ?array
    {
        $refreshToken = $this->refreshTokenManager->get($refreshTokenString);
        
        if (!$refreshToken || !$refreshToken->isValid()) {
            return null;
        }
        
        // Get user
        $username = $refreshToken->getUsername();
        $user = $this->usersRepository->findByEmail($username);
        if (!$user) {
            return null;
        }
        
        // Generate new JWT token
        $token = $this->jwtManager->create($user);
        
        // Generate new refresh token
        $newRefreshToken = $this->refreshTokenManager->create();
        $newRefreshToken->setUsername($user->getUserIdentifier());
        $newRefreshToken->setRefreshToken();
        $newRefreshToken->setValid(new \DateTimeImmutable('+7 days'));
        
        $this->refreshTokenManager->save($newRefreshToken);
        
        // Invalidate old refresh token
        $this->refreshTokenManager->delete($refreshToken);
        
        return [
            'token' => $token,
            'refresh_token' => $newRefreshToken->getRefreshToken(),
            'expires_in' => 600 // 10 minutes
        ];
    }
    
    public function logout(string $refreshTokenString): bool
    {
        $refreshToken = $this->refreshTokenManager->get($refreshTokenString);
        
        if (!$refreshToken) {
            return false;
        }
        
        $this->refreshTokenManager->delete($refreshToken);
        return true;
    }
}