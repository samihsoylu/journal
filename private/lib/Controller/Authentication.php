<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Service\NotificationService;
use App\Utility\Redirect;
use LogicException;

class Authentication extends AbstractController
{
    // Route url constants, to keep paths consistent within multiple classes
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
        //$this->ensureUserHasAdminRights();

        // 'if' Stops displaying 'username, password, email is wrong' error on initial visit
        if ($this->requestIsPost()) {
            try {
                $this->service->register(
                    $_POST['username'] ?? null,
                    $_POST['password'] ?? null,
                    $_POST['email'] ?? null
                );

                $this->getNotificationService()->setNotification(
                    NotificationService::TYPE_SUCCESS,
                    'Registration successful');
            } catch (LogicException $e) {
                $this->getNotificationService()->setNotification(
                    NotificationService::TYPE_ERROR,
                    $e->getMessage());
            }
        }

        $this->addToBladeParameters('post_url', self::REGISTER_URL);
        $this->render('authenticate/register');
    }

    public function login(): void
    {
        $this->ensureUserIsNotLoggedIn();

        // 'if' Stops displaying 'username, password is wrong' error on initial visit
        if ($this->requestIsPost()) {
            try {
                $this->service->login($_POST['username'] ?? null, $_POST['password'] ?? null);

                Redirect::to(Welcome::HOME_URL);
            } catch (LogicException $e) {
                $this->getNotificationService()->setNotification(NotificationService::TYPE_ERROR, $e->getMessage());
            }
        }

        $this->addToBladeParameters('post_url', self::LOGIN_URL);
        $this->render('authenticate/login');
    }

    public function logout(): void
    {
        $this->service->logout();

        $this->getNotificationService()->setNotification('info', 'You have been logged out');
        Redirect::to(self::LOGIN_URL);
    }
}
