<?php declare(strict_types=1);

namespace App\Controller;

use App\Utilities\Redirect;

class Authentication extends AbstractController
{
    public const AUTH_LOGIN_URL = BASE_URL . '/auth/login';
    public const AUTH_LOGOUT_URL = BASE_URL . '/auth/logout';
    public const AUTH_REGISTER_URL = BASE_URL . '/auth/register';

    public function register(): void
    {
        //Redirect::to(BASE_URL . '/authentication/success/');
    }

    public function login(array $vars): void
    {
        print_r($vars);
        echo 'Login';
    }

    public function logout(): void
    {
    }
}
