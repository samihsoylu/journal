<?php declare(strict_types=1);

namespace App\Console\Command\User;

use App\Database\Model\User;
use App\Service\UserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetAll extends Command
{
    public function configure(): void
    {
        $this->setName('user:list')
            ->setDescription('List all users')
            ->setHelp("This command lists all users in the system");
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $service = new UserService();
        $users   = $service->getAllUsers();

        $table = new Table($output);
        $table->setStyle('symfony-style-guide');
        $table->setHeaders(['ID', 'Username', 'Privilege Level'])
            ->setRows(
                array_map(
                    function (User $row) {
                        return [
                            $row->getId(),
                            $row->getUsername(),
                            $row->getPrivilegeLevelAsString()
                        ];
                    },
                    $users
                )
            );

        $table->render();

        return 0;
    }
}
