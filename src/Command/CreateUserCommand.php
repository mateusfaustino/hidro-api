<?php

namespace App\Command;

use App\Domain\Users\User;
use App\Domain\Users\UsersRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserCommand extends Command
{
    protected static $defaultName = 'app:create-user';
    protected static $defaultDescription = 'Create a test user';
    
    private UsersRepository $usersRepository;
    private UserPasswordHasherInterface $passwordHasher;
    
    public function __construct(
        UsersRepository $usersRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
        $this->usersRepository = $usersRepository;
        $this->passwordHasher = $passwordHasher;
    }
    
    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('password', InputArgument::REQUIRED, 'User password')
            ->addArgument('name', InputArgument::REQUIRED, 'User name');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $name = $input->getArgument('name');
        
        // Check if user already exists
        $existingUser = $this->usersRepository->findByEmail($email);
        if ($existingUser) {
            $output->writeln('User with this email already exists.');
            return Command::FAILURE;
        }
        
        // Create new user
        $user = new User(\Symfony\Component\Uid\Uuid::v4()->toRfc4122(), $email, $name);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        
        // Save user
        $this->usersRepository->save($user);
        
        $output->writeln('User created successfully!');
        $output->writeln('Email: ' . $email);
        $output->writeln('Name: ' . $name);
        
        return Command::SUCCESS;
    }
}