<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\DTO\User\CreateSchoolAdminDTO;
use App\Application\UseCase\User\CreateSchoolAdminUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a test school admin user',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly CreateSchoolAdminUseCase $createSchoolAdminUseCase
    ) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('name', InputArgument::REQUIRED, 'User name')
            ->addArgument('password', InputArgument::REQUIRED, 'User password')
            ->addOption('school-id', 's', InputOption::VALUE_REQUIRED, 'School ID');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $email = $input->getArgument('email');
        $name = $input->getArgument('name');
        $password = $input->getArgument('password');
        $schoolId = $input->getOption('school-id');
        
        if (!$schoolId) {
            $io->error('School ID is required. Use --school-id option.');
            return Command::FAILURE;
        }
        
        try {
            $dto = new CreateSchoolAdminDTO(
                email: $email,
                name: $name,
                password: $password,
                schoolId: $schoolId
            );
            
            $response = $this->createSchoolAdminUseCase->execute($dto);
            
            $io->success('School Admin user created successfully!');
            $io->table(
                ['Field', 'Value'],
                [
                    ['ID', $response->id],
                    ['Email', $response->email],
                    ['Name', $response->name],
                    ['Roles', implode(', ', $response->roles)],
                    ['Status', $response->status],
                ]
            );
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to create user: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}