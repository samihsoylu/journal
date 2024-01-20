<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Presentation\Console\Journal\User;

use SamihSoylu\Journal\Application\Service\Contract\UserServiceInterface;
use SamihSoylu\Journal\Domain\Entity\Enum\Role;
use SamihSoylu\Utility\Assert;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use UnexpectedValueException;

#[AsCommand(name: 'journal:user:create', description: 'Create a new user')]
final class CreateUserCommand extends Command
{
    private const FIELD_USERNAME = 'username';
    private const FIELD_PASSWORD = 'password';
    private const FIELD_CONFIRM_PASSWORD = 'confirm-password';
    private const FIELD_EMAIL_ADDRESS = 'email-address';
    private const FIELD_ROLE = 'role';

    public function __construct(
        private readonly UserServiceInterface $userService,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addOption(
            name: self::FIELD_USERNAME,
            mode: InputOption::VALUE_REQUIRED,
            description: 'New account username',
        )
        ->addOption(
            name: self::FIELD_PASSWORD,
            mode: InputOption::VALUE_REQUIRED,
            description: 'New account password',
        )
        ->addOption(
            name: self::FIELD_CONFIRM_PASSWORD,
            mode: InputOption::VALUE_REQUIRED,
            description: 'New account password repeated',
        )
        ->addOption(
            name: self::FIELD_EMAIL_ADDRESS,
            mode: InputOption::VALUE_REQUIRED,
            description: 'Your email address',
        )
        ->addOption(
            name: self::FIELD_ROLE,
            mode: InputOption::VALUE_OPTIONAL,
            description: 'New account role (owner, admin, user)',
            default: 'owner',
        )
        ->setHelp('This command creates a new user account');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $interactive = $input->getOption(self::FIELD_USERNAME) === null;

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        if ($input->getOption(self::FIELD_USERNAME) === null) {
            $question = new Question("\nChoose a username: ");
            $username = $helper->ask($input, $output, $question);
            $input->setOption(self::FIELD_USERNAME, $username);
        }

        if ($input->getOption(self::FIELD_PASSWORD) === null) {
            $question = new Question("Choose your password: ");
            $question->setHidden(true);
            $password = $helper->ask($input, $output, $question);
            $input->setOption(self::FIELD_PASSWORD, $password);
        }

        if ($input->getOption(self::FIELD_CONFIRM_PASSWORD) === null) {
            $question = new Question("Retype your chosen password: ");
            $question->setHidden(true);
            $verifyPassword = $helper->ask($input, $output, $question);
            $input->setOption(self::FIELD_CONFIRM_PASSWORD, $verifyPassword);
        }

        if ($input->getOption(self::FIELD_EMAIL_ADDRESS) === null) {
            $question = new Question("\nEnter your email address: ");
            $emailAddress = $helper->ask($input, $output, $question);
            $input->setOption(self::FIELD_EMAIL_ADDRESS, $emailAddress);
        }

        if ($interactive) {
            $role = $input->getOption(self::FIELD_ROLE);
            $question = new Question("Select a role [<comment>{$role}</comment>]: ", $role);
            $role = $helper->ask($input, $output, $question);
            $input->setOption(self::FIELD_ROLE, $role);
        }

        $output->write("\n");
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getOption(self::FIELD_USERNAME);
        $password = $input->getOption(self::FIELD_PASSWORD);
        $confirmPassword = $input->getOption(self::FIELD_CONFIRM_PASSWORD);
        $emailAddress = $input->getOption(self::FIELD_EMAIL_ADDRESS);
        $role = Role::tryFrom($input->getOption(self::FIELD_ROLE));

        Assert::notNull($username, 'A username must be provided');
        Assert::notNull($password, 'A password must be provided');
        Assert::notNull($emailAddress, 'A email address must be provided');
        Assert::stringIsFilled($password, 'The provided password is too short');
        Assert::notNull($role, 'The provided role does not exist');

        $this->assertPasswordsMatch($password, $confirmPassword);
        $this->assertIsValidEmailAddress($emailAddress);

        $this->userService->createUser(
            $username,
            $password,
            $emailAddress,
            $role,
        );

        $output->writeln("User '{$username}' registered successfully");

        return self::SUCCESS;
    }

    private function assertPasswordsMatch(string $password, string $confirmPassword): void
    {
        if ($password !== $confirmPassword) {
            throw new UnexpectedValueException(
                'Both provided passwords do not match'
            );
        }
    }

    private function assertIsValidEmailAddress(string $emailAddress): void
    {
        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            throw new UnexpectedValueException("Email address '{$emailAddress}' is not a valid email address");
        }
    }
}
