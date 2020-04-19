<?php declare(strict_types=1);

namespace App\Controller;

use App\Database\Exception\NotFoundException;
use App\Database\Repository\UserRepository;
use App\Service\AuthService;
use App\Utilities\Redirect;
use App\Utilities\Session;
use Doctrine\ORM\Tools\Pagination\Paginator;

class Auth extends AbstractController
{
    public const LOGIN_URL = BASE_URL . '/auth/login';
    public const LOGOUT_URL = BASE_URL . '/auth/logout';
    public const REGISTER_URL = BASE_URL . '/auth/register';

    public AuthService $service;

    public function __construct(array $routeParameters)
    {
        parent::__construct($routeParameters);

        $this->service = new AuthService();
    }

    public function register(): void
    {
        //Redirect::to(BASE_URL . '/authenticate/success/');
    }

    public function login(): void
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        try {
            $this->service->login($username, $password);

            Redirect::to(Welcome::HOME_URL);
        } catch (\Throwable $e) {
            $this->addToTemplateParameters('error_message', $e->getMessage());

            echo $this->getBladeInstance()->render('auth/login', $this->getBladeParameters());
        }
    }

    public function logout(): void
    {
        Session::destroy();

        Redirect::to(BASE_URL . '/');
    }
}
