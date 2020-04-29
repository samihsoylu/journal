<?php

namespace App\Controller;

use App\Utilities\Session;

class Welcome extends AbstractController
{
    public function index(): void
    {
        $this->ensureUserIsNotLoggedIn();

        $this->addToBladeParameters('post_url', Authentication::LOGIN_URL);
        echo $this->getBladeInstance()->render('authenticate/login', $this->getBladeParameters());
    }

    public function welcome(): void
    {
        $this->ensureUserIsLoggedIn();

        echo $this->getBladeInstance()->render('home');
    }
}