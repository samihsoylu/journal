<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\UserService;
use App\Utility\Notification;
use App\Utility\Sanitize;
use App\Validator\UserValidator;
use App\Database\Model\User as UserModel;

class User extends AbstractController
{
    public const USERS_URL = BASE_URL . '/users';
    public const USER_URL  = BASE_URL . '/user';

    public const CREATE_USER_URL      = self::USER_URL . '/create';
    public const CREATE_USER_POST_URL = self::CREATE_USER_URL . '/action';

    public const VIEW_USER_URL   = self::USER_URL . '/{id:\d+}';
    public const DELETE_USER_URL = self::VIEW_USER_URL . '/delete/{antiCsrfToken}';

    private UserValidator $validator;
    private UserService $service;

    public function __construct(array $routeParameters)
    {
        parent::__construct($routeParameters);

        // for every action in this controller, the user must be logged in and have admin rights
        $this->redirectLoggedOutUsersToLoginPage();
        $this->ensureUserHasAdminPrivileges();

        $this->validator = new UserValidator($_POST);
        $this->service   = new UserService();
    }

    /**
     * Display all existing users
     *
     * @return void
     */
    public function indexView(): void
    {
        $users = $this->service->getAllUsers();

        $this->template->setVariable('users', $users);
        $this->template->render('user/all');
    }

    /**
     * Display a single user
     *
     * @return void
     */
    public function userView(): void
    {
        $requestedUserId = Sanitize::int($this->getRouteParameters()['id']);

        $user = $this->service->getUserForLoggedInUser($this->getUserId(), $requestedUserId);

        $this->template->setVariable('user', $user);
        $this->template->render('user/view');
    }

    public function createView(): void
    {
        $this->template->setVariable('allowedPrivilegeLevels', UserModel::ALLOWED_PRIVILEGE_LEVELS);
        $this->template->render('user/create');
    }

    public function create(): void
    {
        /** @see UserValidator::create() */
        $this->validator->validate(__FUNCTION__);

        $username = Sanitize::string($_POST['username'], 'strip');
        $email    = Sanitize::string($_POST['email'], 'strip');
        $password = $_POST['password'];

        $this->service->register($username, $password, $email);

        // Present success message
        $this->setNotification(
            Notification::TYPE_SUCCESS,
            'Registration successful'
        );

        $this->createView();
    }
}
