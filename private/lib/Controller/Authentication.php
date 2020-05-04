<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Utility\Redirect;
use LogicException;

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
        // Registration is not open, administrators can only create user accounts
        $this->ensureUserHasAdminRights();

        // If not set, then set as empty string
        $_POST['username'] ??= '';
        $_POST['password'] ??= '';
        $_POST['email']    ??= '';

        // 'if' Stops displaying 'username, password, email is wrong' error on initial visit
        if ($this->isPostRequest()) {
            try {
                $this->service->registerNewUser($_POST['username'], $_POST['password'], $_POST['email']);

                $this->getNotificationService()->setNotification('info', 'Registered successfully!');
            } catch (LogicException $e) {
                $this->addToBladeParameters('error', $e->getMessage());
            }
        }

        // Shows success message after page loads
        $this->checkForNotificationMessages();

        $this->addToBladeParameters('post_url', self::REGISTER_URL);
        $this->render('authenticate/register');
    }

    public function login(): void
    {
        $this->ensureUserIsNotLoggedIn();

        $_POST['username'] ??= '';
        $_POST['password'] ??= '';

        // 'if' Stops displaying 'username, password is wrong' error on initial visit
        if ($this->isPostRequest()) {
            try {
                $this->service->loginUser($_POST['username'], $_POST['password']);

                Redirect::to(Welcome::HOME_URL);
            } catch (LogicException $e) {
                $this->addToBladeParameters('error', $e->getMessage());
            }
        }

        // If previous action was a successful logout, this helps display that success message
        $this->checkForNotificationMessages();

        $this->addToBladeParameters('post_url', self::LOGIN_URL);
         $this->render('authenticate/login');
    }

    public function logout(): void
    {
        $this->service->logoutUser();

        $this->getNotificationService()->setNotification('success', 'Logout successful!');
        Redirect::to(self::LOGIN_URL);
    }
}
