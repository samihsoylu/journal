<?php

declare(strict_types=1);

namespace App\Console\Command\User;

use App\Service\UserService;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'user:delete',
    description: 'Delete a user',
    help: 'This command deletes a user\'s account',
)]
final readonly class Delete
{
    public function __construct(
        private UserService $userService,
    ) {}

    public function __invoke(
        #[Argument(description: 'User id')]
        int $id,
        OutputInterface $output,
    ) : int {
        $user = $this->userService->getUser($id);
        $this->userService->deleteUser($user);

        $output->writeln(sprintf('User %s deleted successfully', $user->getUsername()));

        return Command::SUCCESS;
    }
}
