<?php

namespace App\Controller;

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
        $this->ensureUserIsLoggedIn();
        $this->template->render('dashboard');
    }
}
