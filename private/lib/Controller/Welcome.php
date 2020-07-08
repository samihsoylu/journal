<?php

namespace App\Controller;

class Welcome extends AbstractController
{
    public const HOME_URL = BASE_URL . '/dashboard';

    public function index(): void
    {
        // direct new visitors to login
        (new Authentication([]))->login();
    }

    public function dashboard(): void
    {
        $this->ensureUserIsLoggedIn();
        $this->render('dashboard');
    }
}