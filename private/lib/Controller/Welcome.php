<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Service\EntryService;
use App\Utility\Template;

final class Welcome extends AbstractController
{
    public const string DASHBOARD_URL = BASE_URL . '/dashboard';

    public function __construct(
        AuthenticationService $authenticationService,
        Template $template,
        private readonly EntryService $entryService,
        private readonly Authentication $authenticationController,
    ) {
        parent::__construct($authenticationService, $template);
    }

    public function index() : void
    {
        // direct new visitors to login
        $this->authenticationController->loginView();
    }

    public function dashboard() : void
    {
        $this->redirectLoggedOutUsersToLoginPage();

        $entries = $this->entryService->getAllEntriesForUserFromFilter(
            $this->getUserId(),
            null,
            null,
            null,
            null,
            1,
            5,
        );

        $this->template->setVariable('entries', $entries);
        $this->renderTemplate('dashboard');
    }
}
