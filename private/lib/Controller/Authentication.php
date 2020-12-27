<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Utility\Notification;
use App\Utility\Redirect;
use App\Utility\Sanitize;
use App\Utility\Session;
use App\Validator\AuthenticationValidator;

class Authentication extends AbstractController
{
    // Route url constants, to keep paths consistent within multiple classes
    public const LOGIN_URL         = BASE_URL . '/login';
    public const LOGIN_POST_URL    = self::LOGIN_URL . '/action';
    public const LOGOUT_URL        = BASE_URL . '/logout';

    private AuthenticationService $service;
    private AuthenticationValidator $validator;

    public function __construct(array $routeParameters)
    {
        parent::__construct($routeParameters);

        $this->service   = new AuthenticationService();
        $this->validator = new AuthenticationValidator($_POST);
    }

    /**
     * Login a user
     *
     * @return void
     */
    public function login(): void
    {
        $this->redirectLoggedInUsersToDashboard();

        /** @see AuthenticationValidator::login() */
        $this->validator->validate(__FUNCTION__);

        $username = Sanitize::string($_POST['username'], 'strip');
        $password = $_POST['password'];

        // Log the user in
        $this->service->login($username, $password);

        /** @see AbstractController::redirectLoggedOutUsersToLoginPage() */
        $referredFrom = Session::get('referred_from');
        if ($referredFrom !== null) {
            Session::delete('referred_from');

            Redirect::to(BASE_URL . "/{$referredFrom}");
        }

        Redirect::to(Welcome::DASHBOARD_URL);
    }

    /**
     * Display a login form
     *
     * @return void
     */
    public function loginView(): void
    {
        $this->redirectLoggedInUsersToDashboard();

        $this->template->render('authenticate/login');
    }

    /**
     * Logout a user
     *
     * @return void
     */
    public function logout(): void
    {
        $this->service->logout();

        $this->setNotification(Notification::TYPE_INFO, 'You have been logged out');

        Redirect::to(self::LOGIN_URL);
    }
}
