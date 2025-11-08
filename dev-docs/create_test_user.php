<?php

use App\Domain\Users\User;

require 'vendor/autoload.php';

// Create entity manager
$kernel = new App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();

$entityManager = $container->get('doctrine.orm.entity_manager');
$passwordHasher = $container->get('security.user_password_hasher');

// Create a test user
$user = new User(\Symfony\Component\Uid\Uuid::v4()->toRfc4122(), 'test@example.com', 'Test User');
$hashedPassword = $passwordHasher->hashPassword($user, 'password');
$user->setPassword($hashedPassword);

// Save the user
$entityManager->persist($user);
$entityManager->flush();

echo "Test user created successfully!\n";
echo "Email: test@example.com\n";
echo "Password: password\n";