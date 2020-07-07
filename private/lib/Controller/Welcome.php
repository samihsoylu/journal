<?php

namespace App\Controller;

use App\Utility\Session;

class Welcome extends AbstractController
{
    public const HOME_URL = BASE_URL . '/dashboard';

    public function __construct(array $routeParameters)
    {
        parent::__construct($routeParameters);

        // for every action in this controller, the user must be logged in
        $this->ensureUserIsLoggedIn();
    }

    public function index(): void
    {
        $this->render('dashboard');
    }
}