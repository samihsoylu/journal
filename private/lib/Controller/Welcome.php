<?php

namespace App\Controller;

use App\Utility\Session;

class Welcome extends AbstractController
{
    public const HOME_URL = BASE_URL . '/welcome';

    public function index(): void
    {
        $this->ensureUserIsLoggedIn();

        $this->render('dashboard');
    }
}