<?php

declare(strict_types=1);

namespace App\Console\Command\User;

use App\Database\Model\User;
use App\Service\UserManagementService;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'user:create',
    description: 'Create a new user',
    help: 'This command creates a new user account',
)]
final readonly class Create
{
    public function __construct(private UserManagementService $userManagementService) {}

    public function __invoke(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);

        $username = $io->ask('Username');
        $password = $io->askHidden('Password');
        $confirmPassword = $io->askHidden('Retype password');
        $email = $io->ask('Email address');

        $allowedPrivilegeLevels = implode(', ', User::ALLOWED_PRIVILEGE_LEVELS);
        $privilegeLevel = $io->ask(sprintf('Privilege Level (%s)', $allowedPrivilegeLevels), 'Owner');

        if ($password !== $confirmPassword) {
            throw new RuntimeException('Both provided passwords do not match');
        }

        if ( ! in_array($privilegeLevel, User::ALLOWED_PRIVILEGE_LEVELS, true)) {
            throw new RuntimeException('The provided privilege level does not exist');
        }

        $privilegeLevels = array_flip(User::ALLOWED_PRIVILEGE_LEVELS);

        $this->userManagementService->createUser($username, $password, $email, $privilegeLevels[$privilegeLevel]);

        $io->success(sprintf('User %s registered successfully', $username));

        return Command::SUCCESS;
    }
}
