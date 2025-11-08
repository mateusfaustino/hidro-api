<?php

namespace App\Infrastructure\Security;

use App\Domain\Auth\Exception\InvalidRefreshTokenException;
use App\Domain\Auth\TokenManagerInterface;
use App\Domain\Auth\TokenPair;
use App\Domain\Users\User;
use App\Domain\Users\UsersRepository;
use App\Infrastructure\Persistence\Doctrine\Entity\RefreshToken as DoctrineRefreshToken;
use App\Infrastructure\Persistence\Doctrine\Repository\RefreshTokenRepository;
use DateInterval;
use DateTimeImmutable;

class JwtTokenManager implements TokenManagerInterface
{
    public function __construct(
        private readonly UsersRepository $usersRepository,
        private readonly RefreshTokenRepository $refreshTokenRepository,
        private readonly RefreshTokenHasher $refreshTokenHasher,
        private readonly JwtEncoder $jwtEncoder,
        private readonly int $accessTokenTtl,
        private readonly int $refreshTokenTtl,
    ) {
    }

    public function issueTokenPair(User $user, ?string $ipAddress = null, ?string $userAgent = null): TokenPair
    {
        $issuedAt = new DateTimeImmutable();
        $accessExpiresAt = $issuedAt->add(new DateInterval(sprintf('PT%dS', $this->accessTokenTtl)));
        $refreshExpiresAt = $issuedAt->add(new DateInterval(sprintf('PT%dS', $this->refreshTokenTtl)));

        $payload = $this->jwtEncoder->buildPayload($issuedAt, $accessExpiresAt, [
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);

        $accessToken = $this->jwtEncoder->encode($payload);

        $plainRefreshToken = $this->generateRefreshToken();
        $hashedRefreshToken = $this->refreshTokenHasher->hash($plainRefreshToken);

        $refreshTokenEntity = new DoctrineRefreshToken($user->getEmail(), $hashedRefreshToken, $refreshExpiresAt);
        $refreshTokenEntity->setCreatedByIp($ipAddress);
        $refreshTokenEntity->setUserAgent($userAgent);
        $this->refreshTokenRepository->save($refreshTokenEntity);

        return new TokenPair(
            $accessToken,
            $plainRefreshToken,
            $issuedAt,
            $accessExpiresAt,
            $refreshExpiresAt,
            'Bearer',
            $user->getRoles(),
        );
    }

    public function rotateRefreshToken(string $refreshToken, ?string $ipAddress = null, ?string $userAgent = null): TokenPair
    {
        $hashedRefreshToken = $this->refreshTokenHasher->hash($refreshToken);
        $now = new DateTimeImmutable();
        $existingToken = $this->refreshTokenRepository->findValidToken($hashedRefreshToken, $now);

        if ($existingToken === null) {
            throw new InvalidRefreshTokenException('Invalid or expired refresh token.');
        }

        if ($existingToken->isExpired($now)) {
            $this->refreshTokenRepository->delete($existingToken);
            throw new InvalidRefreshTokenException('Expired refresh token.');
        }

        if ($existingToken->isRevoked()) {
            throw new InvalidRefreshTokenException('Refresh token has been revoked.');
        }

        $user = $this->usersRepository->findByEmail($existingToken->getUsername());

        if ($user === null) {
            $this->refreshTokenRepository->delete($existingToken);
            throw new InvalidRefreshTokenException('Associated user not found.');
        }

        $existingToken->markRevoked();
        $this->refreshTokenRepository->save($existingToken);

        return $this->issueTokenPair($user, $ipAddress, $userAgent);
    }

    public function revokeRefreshToken(string $refreshToken, ?string $ipAddress = null): void
    {
        $hashedRefreshToken = $this->refreshTokenHasher->hash($refreshToken);
        $this->refreshTokenRepository->revokeByHashedToken($hashedRefreshToken);
    }

    private function generateRefreshToken(): string
    {
        return bin2hex(random_bytes(64));
    }
}
