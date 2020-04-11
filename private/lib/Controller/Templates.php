<?php

namespace App\Controller;

class Templates extends AbstractController
{
    public function login(): void
    {
        $params = $this->templateParams;
        $params['post_url'] = Authentication::AUTH_LOGIN_URL;

        echo $this->template->render('authentication/login', $params);
    }
}