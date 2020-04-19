<?php

namespace App\Controller;

use App\Utilities\Session;

class Welcome extends AbstractController
{
    public const HOME_URL = BASE_URL . '/welcome';

    public function index(): void
    {
        if (Session::get('isLoggedIn')) {
            echo $this->getBladeInstance()->render('home');
            exit();
        }

        $this->addToTemplateParameters('post_url', Auth::LOGIN_URL);
        echo $this->getBladeInstance()->render('authenticate/login', $this->getBladeParameters());
    }
}