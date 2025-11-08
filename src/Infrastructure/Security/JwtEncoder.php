<?php

namespace App\Infrastructure\Security;

use DateTimeImmutable;
use DomainException;

class JwtEncoder
{
    public function __construct(
        private readonly string $privateKeyPath,
        private readonly string $privateKeyPassphrase,
        private readonly string $issuer,
        private readonly string $audience,
    ) {
    }

    /**
     * @param array<string, mixed> $claims
     */
    public function encode(array $claims): string
    {
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $encodedHeader = $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR));
        $encodedPayload = $this->base64UrlEncode(json_encode($claims, JSON_THROW_ON_ERROR));

        $data = sprintf('%s.%s', $encodedHeader, $encodedPayload);
        $privateKey = openssl_pkey_get_private(
            file_get_contents($this->privateKeyPath),
            $this->privateKeyPassphrase
        );

        if ($privateKey === false) {
            throw new DomainException('Unable to load private key for JWT signing.');
        }

        $signature = '';
        $result = openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($privateKey);

        if ($result === false) {
            throw new DomainException('Unable to sign JWT token.');
        }

        return sprintf('%s.%s', $data, $this->base64UrlEncode($signature));
    }

    /**
     * @param DateTimeImmutable $issuedAt
     * @param DateTimeImmutable $expiresAt
     * @param array<string, mixed> $customClaims
     */
    public function buildPayload(DateTimeImmutable $issuedAt, DateTimeImmutable $expiresAt, array $customClaims): array
    {
        $payload = array_merge(
            [
                'iss' => $this->issuer,
                'aud' => $this->audience,
                'iat' => $issuedAt->getTimestamp(),
                'nbf' => $issuedAt->getTimestamp(),
                'exp' => $expiresAt->getTimestamp(),
                'jti' => bin2hex(random_bytes(16)),
            ],
            $customClaims
        );

        return $payload;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
