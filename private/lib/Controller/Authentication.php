<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Utilities\Redirect;
use App\Utilities\Session;

class Authentication extends AbstractController
{
    /**
     * URL constants, for keeping consistent urls across multiple locations in this project. These constants are either
     * used in the router, or they are passed on to the templating engine so that a button on the website may link to
     * the defined url in the constants below.
     */
    public const LOGIN_URL    = BASE_URL . '/auth/login';
    public const LOGOUT_URL   = BASE_URL . '/auth/logout';
    public const REGISTER_URL = BASE_URL . '/auth/register';

    /**
     * @var AuthenticationService
     */
    private AuthenticationService $service;

    public function __construct(array $routeParameters)
    {
        parent::__construct($routeParameters);

        $this->service = new AuthenticationService();
    }

    public function register(): void
    {
        // this needs to be looked in to more in depth
        // a few factors to consider
        // we don't want to open registration to the world
        // we want registration only be possible via the admin user
        // and the password perhaps needs to be generated and emailed

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $email    = $_POST['email'] ?? '';

        try {
            $this->service->register($username, $password, $email);

            Session::put('info_message', 'Registration was successful');
            Redirect::to(self::HOME_URL);
        } catch (\Throwable $e) {
            $this->addToBladeParameters('error_message', $e->getMessage());

            echo $this->getBladeInstance()->render('auth/register', $this->getBladeParameters());
        }
    }

    public function login(): void
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        try {
            $this->service->login($username, $password);

            Redirect::to(self::HOME_URL);
        } catch (\Throwable $e) {
            $this->addToBladeParameters('error_message', $e->getMessage());

            echo $this->getBladeInstance()->render('auth/login', $this->getBladeParameters());
        }
    }

    public function logout(): void
    {
        $this->service->logout();

        Redirect::to(BASE_URL . '/');
    }
}
