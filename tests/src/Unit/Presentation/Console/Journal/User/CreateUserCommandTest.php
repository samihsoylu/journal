<?php

declare(strict_types=1);

use SamihSoylu\Journal\Presentation\Console\Journal\User\CreateUserCommand;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Spy\SpyUserService;

it('should invoke user service', function (): void {
    $spyUserService = new SpyUserService();

    $args = [
        '--username' => 'fakeaccount',
        '--password' => 'fakepassword',
        '--confirm-password' => 'fakepassword',
        '--email-address' => 'fake@email.com',
        '--role' => 'owner',
    ];

    testKit()->executeConsoleCommand(new CreateUserCommand($spyUserService), $args);

    $spyUserService->assertMethodInvoked('createUser');
});

it('should invoke user service in interactive mode', function (): void {
    $spyUserService = new SpyUserService();

    testKit()
        ->executeConsoleCommand(
            new CreateUserCommand($spyUserService),
            options: ['interactive' => true],
            inputs: [
                'fake-username',
                'fake-password',
                'fake-password',
                'fake@mail.com',
                'owner',
            ]
        );

    $spyUserService->assertMethodInvoked('createUser');
});

it('should throw on a missing field', function (): void {
    $args = [
        //'--username' => 'fakeaccount',
        '--password' => 'fakepassword',
        '--confirm-password' => 'fakepassword',
        '--email-address' => 'fake@email.com',
        '--role' => 'owner',
    ];

    testKit()->executeConsoleCommand(new CreateUserCommand(new SpyUserService()), $args);
})->throws(UnexpectedValueException::class)
    ->expectExceptionMessage('A username must be provided');

it('should throw when provided passwords do not match', function (): void {
    $args = [
        '--username' => 'fakeaccount',
        '--password' => 'fakepassword1234',
        '--confirm-password' => 'fakepassword',
        '--email-address' => 'fake@email.com',
        '--role' => 'owner',
    ];

    testKit()->executeConsoleCommand(new CreateUserCommand(new SpyUserService()), $args);
})->throws(UnexpectedValueException::class)
    ->expectExceptionMessage('Both provided passwords do not match');

it('should throw when bad email is provided', function (): void {
    $args = [
        '--username' => 'fakeaccount',
        '--password' => 'fakepassword',
        '--confirm-password' => 'fakepassword',
        '--email-address' => 'badmail.com',
        '--role' => 'owner',
    ];

    testKit()->executeConsoleCommand(new CreateUserCommand(new SpyUserService()), $args);
})->throws(UnexpectedValueException::class)
    ->expectExceptionMessage("Email address 'badmail.com' is not a valid email address");
