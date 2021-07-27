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

class Create extends Command
{
    private const FIELD_USERNAME = 'Username';
    private const FIELD_PASSWORD = 'Password';
    private const FIELD_CONFIRM_PASSWORD = 'ConfirmPassword';
    private const FIELD_EMAIL_ADDRESS = 'EmailAddress';
    private const FIELD_PRIVILEGE_LEVEL = 'PrivilegeLevel';

    public function configure()
    {
        $allowedPrivilegeLevels = implode(', ', User::ALLOWED_PRIVILEGE_LEVELS);

        $this->setName('user:create')
            ->setDescription('Create a new user')
            ->addOption(self::FIELD_USERNAME, null, InputArgument::REQUIRED, 'New account username')
            ->addOption(self::FIELD_PASSWORD, null, InputArgument::REQUIRED, 'New account password')
            ->addOption(self::FIELD_CONFIRM_PASSWORD, null, InputArgument::REQUIRED, 'New account password repeated')
            ->addOption(self::FIELD_EMAIL_ADDRESS, null, InputArgument::REQUIRED, 'New account email address')
            ->addOption(self::FIELD_PRIVILEGE_LEVEL, null, InputArgument::OPTIONAL, "New account privilege level ({$allowedPrivilegeLevels})", 'Owner')
            ->setHelp("This command creates a new user account ");
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $question = new Question("Username: ");
        $username = $helper->ask($input, $output, $question);
        $input->setOption(self::FIELD_USERNAME, $username);

        $question = new Question("Password: ");
        $question->setHidden(true);
        $password = $helper->ask($input, $output, $question);
        $input->setOption(self::FIELD_PASSWORD, $password);

        $question = new Question("Retype password: ");
        $question->setHidden(true);
        $verifyPassword = $helper->ask($input, $output, $question);
        $input->setOption(self::FIELD_CONFIRM_PASSWORD, $verifyPassword);

        $question = new Question("Email address: ");
        $emailAddress = $helper->ask($input, $output, $question);
        $input->setOption(self::FIELD_EMAIL_ADDRESS, $emailAddress);

        $privilegeLevel = $input->getOption(self::FIELD_PRIVILEGE_LEVEL);
        $question = new Question("Privilege Level [<comment>{$privilegeLevel}</comment>]: ", $privilegeLevel);
        $privilegeLevel = $helper->ask($input, $output, $question);
        $input->setOption(self::FIELD_PRIVILEGE_LEVEL, $privilegeLevel);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getOption(self::FIELD_USERNAME);
        $password = $input->getOption(self::FIELD_PASSWORD);
        $passwordVerify = $input->getOption(self::FIELD_PASSWORD);
        $email = $input->getOption(self::FIELD_EMAIL_ADDRESS);
        $privilegeLevel = $input->getOption(self::FIELD_PRIVILEGE_LEVEL);

        if ($password !== $passwordVerify) {
            throw new \RuntimeException("Both provided passwords do not match");
        }

        if (!in_array($privilegeLevel, User::ALLOWED_PRIVILEGE_LEVELS)) {
            throw new \RuntimeException("The provided privilege level does not exist");
        }

        $privilegeLevels = array_flip(User::ALLOWED_PRIVILEGE_LEVELS);

        $service = new UserService();
        $service->createUser($username, $password, $email, $privilegeLevels[$privilegeLevel]);

        $output->writeln("User {$username} registered successfully");

        return 0;
    }
}
