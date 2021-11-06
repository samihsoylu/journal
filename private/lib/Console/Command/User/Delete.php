<?php

namespace App\Console\Command\User;

use App\Database\Model\User;
use App\Service\UserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Delete extends Command
{
    private const FIELD_ID = 'id';

    public function configure(): void
    {
        $this->setName('user:delete')
            ->setDescription('Delete a user')
            ->addArgument(self::FIELD_ID, InputArgument::REQUIRED, 'User id')
            ->setHelp("This command deletes a user's account");
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = (int)$input->getArgument(self::FIELD_ID);

        $service = new UserService();
        $user = $service->getUser($userId);
        $service->deleteUser($user);

        $output->writeln("User {$user->getUsername()} deleted successfully");

        return 0;
    }
}
