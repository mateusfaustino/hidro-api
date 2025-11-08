<?php

namespace App\Tests\Functional;

use App\Domain\Users\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerTest extends WebTestCase
{
    private static bool $schemaCreated = false;

    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $this->createDatabaseSchema();

        $this->loadDefaultUser();
    }

    private function createDatabaseSchema(): void
    {
        if (!self::$schemaCreated) {
            $schemaTool = new SchemaTool($this->entityManager);
            $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
            $schemaTool->dropDatabase();
            if (!empty($metadata)) {
                $schemaTool->createSchema($metadata);
            }
            self::$schemaCreated = true;
            return;
        }

        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        foreach ($this->entityManager->getMetadataFactory()->getAllMetadata() as $metadata) {
            $connection->executeStatement($platform->getTruncateTableSQL($metadata->getTableName(), true));
        }
    }

    private function loadDefaultUser(): void
    {
        $user = new User('user-1', 'john.doe@example.com', 'John Doe');
        $hashed = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashed);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function testLoginWithValidCredentialsReturnsTokenPair(): void
    {
        $client = static::createClient();
        $client->jsonRequest('POST', '/api/v1/auth/login', [
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ]);

        self::assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Bearer', $response['token_type']);
        self::assertArrayHasKey('access_token', $response);
        self::assertArrayHasKey('expires_in', $response);
        self::assertArrayHasKey('refresh_token', $response);
        self::assertArrayHasKey('refresh_expires_in', $response);
        self::assertSame(['ROLE_USER'], $response['scope']);
    }

    public function testLoginWithInvalidCredentialsReturnsUnauthorized(): void
    {
        $client = static::createClient();
        $client->jsonRequest('POST', '/api/v1/auth/login', [
            'email' => 'john.doe@example.com',
            'password' => 'wrong-password',
        ]);

        self::assertResponseStatusCodeSame(401);
        $response = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('invalid_credentials', $response['type']);
    }

    public function testRefreshTokenLifecycle(): void
    {
        $client = static::createClient();
        $client->jsonRequest('POST', '/api/v1/auth/login', [
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ]);

        $loginResponse = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $refreshToken = $loginResponse['refresh_token'];

        $client->jsonRequest('POST', '/api/v1/auth/token/refresh', [
            'refresh_token' => $refreshToken,
        ]);

        self::assertResponseIsSuccessful();
        $refreshResponse = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertNotSame($refreshToken, $refreshResponse['refresh_token']);

        // Ensure old refresh token can no longer be used (single-use rotation)
        $client->jsonRequest('POST', '/api/v1/auth/token/refresh', [
            'refresh_token' => $refreshToken,
        ]);

        self::assertResponseStatusCodeSame(401);
    }

    public function testLogoutRevokesRefreshToken(): void
    {
        $client = static::createClient();
        $client->jsonRequest('POST', '/api/v1/auth/login', [
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ]);

        $loginResponse = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $refreshToken = $loginResponse['refresh_token'];

        $client->jsonRequest('POST', '/api/v1/auth/logout', [
            'refresh_token' => $refreshToken,
        ]);

        self::assertResponseStatusCodeSame(204);

        // Ensure refresh token is no longer valid after logout
        $client->jsonRequest('POST', '/api/v1/auth/token/refresh', [
            'refresh_token' => $refreshToken,
        ]);

        self::assertResponseStatusCodeSame(401);
    }
}
