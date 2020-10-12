<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Utility\Notification;
use App\Utility\Redirect;
use App\Utility\Sanitizer;
use App\Validator\AuthenticationValidator;

class Authentication extends AbstractController
{
    // Route url constants, to keep paths consistent within multiple classes
    public const LOGIN_URL         = BASE_URL . '/login';
    public const LOGIN_POST_URL    = self::LOGIN_URL . '/action';
    public const REGISTER_URL      = BASE_URL . '/register';
    public const REGISTER_POST_URL = self::REGISTER_URL . '/action';
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
     * Action for post request when registration is submitted in /auth/register
     * Url: /auth/register/action
     *
     * @return void
     */
    public function register(): void
    {
        // Registration is not open, administrators can only create user accounts
        //$this->ensureUserHasAdminRights();

        /** @see AuthenticationValidator::register() */
        $this->validator->validate(__FUNCTION__);

        $username = Sanitizer::sanitizeString($_POST['username'], 'trim|lowercase');
        $email    = Sanitizer::sanitizeString($_POST['email'], 'trim|lowercase');
        $password = $_POST['password'];

        // Register the user
        $this->service->register(
            $username,
            $password,
            $email
        );

        // Present success message
        $this->setNotification(
            Notification::TYPE_SUCCESS,
            'Registration successful'
        );

        $this->registerView();
    }

    public function registerView(): void
    {
        $this->template->render('authenticate/register');
    }

    /**
     * Action for post request when login details are submitted on /auth/login
     * Url: /auth/login/action
     *
     * @return void
     */
    public function login(): void
    {
        $this->redirectLoggedInUsersToDashboard();

        /** @see AuthenticationValidator::login() */
        $this->validator->validate(__FUNCTION__);

        $username = Sanitizer::sanitizeString($_POST['username'], 'trim|lowercase');
        $password = $_POST['password'];

        // Log the user in
        $this->service->login($username, $password);

        Redirect::to(Welcome::DASHBOARD_URL);
    }

    public function loginView(): void
    {
        $this->redirectLoggedInUsersToDashboard();

        $this->template->render('authenticate/login');
    }

    /**
     * Action for logging out a user, triggered when visiting /auth/logout
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
