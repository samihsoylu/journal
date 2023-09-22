<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Presentation\Console\Journal\User;

use SamihSoylu\Journal\Application\Core\User\UseCase\Create\CreateUserAction;
use SamihSoylu\Journal\Application\Workflow\CreateUserFlow;
use SamihSoylu\Journal\Application\Workflow\Dto\CreateUserFlowDto;
use SamihSoylu\Journal\Domain\Entity\Enum\Role;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionDispatcherInterface;
use SamihSoylu\Journal\Infrastructure\Port\Cache\Cacheable;
use SamihSoylu\Utility\Assert;
use SamihSoylu\Utility\StringHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use UnexpectedValueException;

#[AsCommand(name: 'journal:user:create', description: 'Create a new user')]
final class CreateUserCommand extends Command
{
    private const FIELD_USERNAME = 'username';
    private const FIELD_PASSWORD = 'password';
    private const FIELD_CONFIRM_PASSWORD = 'confirm-password';
    private const FIELD_ROLE = 'role';

    public function __construct(
        private readonly ActionDispatcherInterface $actionDispatcher,
        private Cacheable $encryptedTransientCache,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addOption(self::FIELD_USERNAME, null, InputArgument::REQUIRED, 'New account username')
            ->addOption(self::FIELD_PASSWORD, null, InputArgument::REQUIRED, 'New account password')
            ->addOption(self::FIELD_CONFIRM_PASSWORD, null, InputArgument::REQUIRED, 'New account password repeated')
            ->addOption(self::FIELD_ROLE, null, InputArgument::OPTIONAL, 'New account role (owner, admin, user)', 'owner')
            ->setHelp("This command creates a new user account ");
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $question = new Question("\nChoose a username: ");
        $username = $helper->ask($input, $output, $question);
        $input->setOption(self::FIELD_USERNAME, $username);

        $question = new Question("Choose your password: ");
        $question->setHidden(true);
        $password = $helper->ask($input, $output, $question);
        $input->setOption(self::FIELD_PASSWORD, $password);

        $question = new Question("Retype your chosen password: ");
        $question->setHidden(true);
        $verifyPassword = $helper->ask($input, $output, $question);
        $input->setOption(self::FIELD_CONFIRM_PASSWORD, $verifyPassword);

        $role = $input->getOption(self::FIELD_ROLE);
        $question = new Question("Select a role [<comment>{$role}</comment>]: ", $role);
        $role = $helper->ask($input, $output, $question);
        $input->setOption(self::FIELD_ROLE, $role);

        $output->write("\n");
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getOption(self::FIELD_USERNAME);
        $password = $input->getOption(self::FIELD_PASSWORD);
        $confirmPassword = $input->getOption(self::FIELD_CONFIRM_PASSWORD);
        $role = Role::tryFrom($input->getOption(self::FIELD_ROLE));

        Assert::notNull($username, 'A username must be provided');
        Assert::notNull($password, 'A password must be provided');
        Assert::stringIsFilled($password, 'The provided password is too short');
        Assert::notNull($role, 'The provided role does not exist');

        $this->assertPasswordsMatch($password, $confirmPassword);

        $passwordTransientCacheId = $this->storePasswordInEncryptedCache($password);
        $this->actionDispatcher->dispatch(new CreateUserAction(
            $username,
            $passwordTransientCacheId,
            $role,
        ));

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

    private function storePasswordInEncryptedCache(string $password): string
    {
        $transientId = StringHelper::createRandomString();
        $this->encryptedTransientCache->set($transientId, $password);

        return $transientId;
    }
}