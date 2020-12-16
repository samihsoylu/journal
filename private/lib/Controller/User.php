<?php

namespace App\Controller;

use App\Service\UserService;
use App\Utility\UserSession;
use App\Validator\UserValidator;

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
        $this->ensureUserHasAdminRights();

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
        $userId = $this->getRouteParameters()['id'];

        $requestedUser = $this->service->getUser($userId);
        $loggedInUser  = UserSession::getUserObject();

        $this->template->setVariables([
            'user'            => $requestedUser,
            'isReadOnly'      => !$this->service->getHelper()->loggedInUserHasUpdatePrivilegesForThisUser(
                $requestedUser,
                $loggedInUser
            ),
            'totalEntries'    => $this->service->getEntryService()->getEntryCountForUser($requestedUser),
            'totalCategories' => $this->service->getCategoryService()->getCategoryCountForUser($requestedUser),
        ]);
        $this->template->render('user/view');
    }
}