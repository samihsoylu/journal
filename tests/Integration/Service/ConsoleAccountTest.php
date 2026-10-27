<?php

declare(strict_types=1);

namespace Tests\Integration\Service;

use App\Console\Command\User\Create;
use App\Console\Command\User\Delete;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\TestFramework\Factory\UserFactory;
use Tests\TestFramework\IntegrationTestCase;

/** @internal
 * @coversNothing
 */
final class ConsoleAccountTest extends IntegrationTestCase
{
    #[Test]
    #[DataProvider('provideInvalid_interactive_answers_create_no_accountCases')]
    public function invalid_interactive_answers_create_no_account(string $confirmation, string $privilege, string $message) : void
    {
        $application = new Application();
        $application->addCommand($this->getService(Create::class));
        $command = new CommandTester($application->get('user:create'));
        $command->setInputs(['console-reader', 'new-password', $confirmation, 'console@example.test', $privilege]);

        try {
            $command->execute([]);
            self::fail('Invalid interactive answers created an account');
        } catch (RuntimeException $exception) {
            self::assertSame($message, $exception->getMessage());
            self::assertSame([], $this->testOrm->fetchAllAssoc("SELECT id FROM users WHERE username='console-reader'"));
        }
    }

    public static function provideInvalid_interactive_answers_create_no_accountCases() : iterable
    {
        return [
            ['different', 'User', 'Both provided passwords do not match'],
            ['new-password', 'Root', 'The provided privilege level does not exist'],
        ];
    }

    #[Test]
    public function console_deletion_removes_the_target_and_reports_its_name() : void
    {
        $user = UserFactory::setup()->create();
        $id = $user->getId();
        $application = new Application();
        $application->addCommand($this->getService(Delete::class));
        $command = new CommandTester($application->get('user:delete'));
        self::assertSame(0, $command->execute(['id' => $id]));
        self::assertStringContainsString('User testuser deleted successfully', $command->getDisplay());
        self::assertSame([], $this->testOrm->fetchAllAssoc('SELECT id FROM users WHERE id = ?', [$id]));
    }
}
