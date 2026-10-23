<?php

declare(strict_types=1);

namespace App\Console\Command\User;

use App\Database\Model\User;
use App\Service\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'user:list',
    description: 'List all users',
    help: 'This command lists all users in the system',
)]
final readonly class GetAll
{
    public function __construct(private UserService $userService) {}

    public function __invoke(OutputInterface $output) : int
    {
        $users = $this->userService->getAllUsers();

        $table = new Table($output);
        $table->setStyle('symfony-style-guide');
        $table->setHeaders(['ID', 'Username', 'Privilege Level'])
            ->setRows(
                array_map(
                    static fn (User $row) : array => [
                        $row->getId(),
                        $row->getUsername(),
                        $row->getPrivilegeLevelAsString(),
                    ],
                    $users,
                ),
            )
        ;

        $table->render();

        return Command::SUCCESS;
    }
}
