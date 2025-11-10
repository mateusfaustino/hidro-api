<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Uid\Uuid;

#[AsCommand(
    name: 'app:seed-users',
    description: 'Seed database with test users for each role',
)]
class SeedUsersCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory
    ) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->addOption('school-id', 's', InputOption::VALUE_OPTIONAL, 'School ID for users', 'school-001')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force seed even if users already exist');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $schoolId = $input->getOption('school-id');
        $force = $input->getOption('force');
        
        $io->title('🌱 Seeding Users Database');
        $io->info('Creating test users for all roles with password: "password"');
        
        // Hash password using bcrypt
        $hashedPassword = password_hash('password', PASSWORD_BCRYPT);
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        
        $users = [
            [
                'id' => Uuid::v4()->toRfc4122(),
                'role' => 'ROLE_SCHOOL_ADMIN',
                'email' => 'admin@escola.com',
                'name' => 'Administrador da Escola',
                'phone' => '+5511987654321',
                'school_id' => $schoolId,
                'status' => 'active',
            ],
            [
                'id' => Uuid::v4()->toRfc4122(),
                'role' => 'ROLE_SECRETARY',
                'email' => 'secretaria@escola.com',
                'name' => 'Maria Secretária',
                'phone' => '+5511987654322',
                'school_id' => $schoolId,
                'status' => 'active',
            ],
            [
                'id' => Uuid::v4()->toRfc4122(),
                'role' => 'ROLE_TEACHER',
                'email' => 'professor@escola.com',
                'name' => 'João Professor',
                'phone' => '+5511987654323',
                'school_id' => $schoolId,
                'status' => 'active',
            ],
            [
                'id' => Uuid::v4()->toRfc4122(),
                'role' => 'ROLE_GUARDIAN',
                'email' => 'responsavel@email.com',
                'name' => 'Ana Responsável',
                'phone' => '+5511987654324',
                'school_id' => $schoolId,
                'status' => 'pending',
            ],
            [
                'id' => Uuid::v4()->toRfc4122(),
                'role' => 'ROLE_SAAS_SUPPORT',
                'email' => 'suporte@hidro.com',
                'name' => 'Suporte SaaS',
                'phone' => '+5511987654325',
                'school_id' => null,
                'status' => 'active',
            ],
        ];
        
        $created = 0;
        $skipped = 0;
        
        foreach ($users as $userData) {
            // Check if user already exists
            $existing = $this->connection->fetchOne(
                'SELECT COUNT(*) FROM users WHERE email = ?',
                [$userData['email']]
            );
            
            if (!$force && $existing > 0) {
                $io->warning("User {$userData['email']} ({$userData['role']}) already exists. Skipping...");
                $skipped++;
                continue;
            }
            
            if ($existing > 0) {
                // Delete existing user if force mode
                $this->connection->executeStatement(
                    'DELETE FROM users WHERE email = ?',
                    [$userData['email']]
                );
            }
            
            try {
                $this->connection->insert('users', [
                    'id' => $userData['id'],
                    'email' => $userData['email'],
                    'name' => $userData['name'],
                    'phone' => $userData['phone'],
                    'password' => $hashedPassword,
                    'roles' => json_encode([$userData['role']]),
                    'status' => $userData['status'],
                    'school_id' => $userData['school_id'],
                    'created_at' => $now,
                ]);
                
                $io->success("✓ Created {$userData['role']}: {$userData['name']} ({$userData['email']})");
                $created++;
            } catch (\Exception $e) {
                $io->error("✗ Failed to create {$userData['role']}: {$e->getMessage()}");
            }
        }
        
        $io->newLine();
        $io->section('📊 Summary');
        $io->table(
            ['Metric', 'Count'],
            [
                ['Created', $created],
                ['Skipped', $skipped],
                ['Total', count($users)],
            ]
        );
        
        if ($created > 0) {
            $io->newLine();
            $io->note([
                'All users have the password: password',
                "All users (except SAAS_SUPPORT) are assigned to school: {$schoolId}",
                '',
                'Test credentials:',
                "  Admin:       admin@escola.com / password",
                "  Secretária:  secretaria@escola.com / password",
                "  Professor:   professor@escola.com / password",
                "  Responsável: responsavel@email.com / password (status: PENDING)",
                "  Suporte:     suporte@hidro.com / password",
            ]);
        }
        
        return Command::SUCCESS;
    }
}
