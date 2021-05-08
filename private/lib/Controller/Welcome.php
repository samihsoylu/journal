<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\EntryService;

class Welcome extends AbstractController
{
    public const DASHBOARD_URL = BASE_URL . '/dashboard';

    public function index(): void
    {
        // direct new visitors to login
        (new Authentication([]))->loginView();
    }

    public function dashboard(): void
    {
        $this->redirectLoggedOutUsersToLoginPage();
        $this->injectSessionVariableToTemplate();

        $service = new EntryService();
        $entries = $service->getAllEntriesForUserFromFilter(
            $this->getUserId(),
            null,
            null,
            null,
            null,
            1,
            5
        );

        $this->template->setVariable('entries', $entries);
        $this->template->render('dashboard');
    }
}
