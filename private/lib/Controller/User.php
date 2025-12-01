<?php

declare(strict_types=1);

namespace App\Controller;

use App\Database\Model\User as UserModel;
use App\Service\AuthenticationService;
use App\Service\UserManagementService;
use App\Service\UserService;
use App\Utility\Notification;
use App\Utility\Redirect;
use App\Utility\Sanitize;
use App\Utility\Template;
use App\Validator\UserValidator;

final class User extends AbstractController
{
    public const string USERS_URL = BASE_URL . '/users';
    public const string USER_URL = BASE_URL . '/user';
    public const string CREATE_USER_URL = self::USER_URL . '/create';
    public const string CREATE_USER_POST_URL = self::CREATE_USER_URL . '/action';
    public const string VIEW_USER_URL = self::USER_URL . '/{id:\d+}';
    public const string DELETE_USER_URL = self::VIEW_USER_URL . '/delete/{antiCsrfToken}';
    public const string UPDATE_USER_URL = self::VIEW_USER_URL . '/update';

    private readonly UserValidator $validator;

    public function __construct(
        AuthenticationService $authenticationService,
        Template $template,
        private readonly UserManagementService $userManagementService,
        private readonly UserService $userService,
        private readonly Sanitize $sanitize,
    ) {
        parent::__construct($authenticationService, $template);

        // for every action in this controller, the user must be logged in and have admin rights
        $this->redirectLoggedOutUsersToLoginPage();
        $this->ensureUserHasAdminPrivileges();

        $this->validator = new UserValidator($_POST, [], $authenticationService->getUserSession());
    }

    /**
     * Display all existing users.
     */
    public function indexView() : void
    {
        $users = $this->userService->getAllUsers();

        $this->template->setVariable('users', $users);
        $this->renderTemplate('user/all');
    }

    /**
     * Create a user.
     */
    public function create() : never
    {
        /** @see UserValidator::create() */
        $this->validator->validate(__FUNCTION__);

        $username = $this->sanitize->string($_POST['username'], [Sanitize::OPTION_LOWERCASE, Sanitize::OPTION_STRIP]);
        $email = $this->sanitize->string($_POST['email'], [Sanitize::OPTION_LOWERCASE, Sanitize::OPTION_STRIP]);
        $privilegeLevel = $this->sanitize->int($_POST['privilegeLevel']);
        $password = $_POST['password'];

        $userId = $this->userManagementService->createUserForAdmin($this->getUserId(), $username, $password, $email, $privilegeLevel);

        // Present success message
        $this->setNotification(
            Notification::TYPE_SUCCESS,
            'Registration successful',
        );

        Redirect::to(self::USER_URL . ('/' . $userId));
    }

    /**
     * Display a create user form.
     */
    public function createView() : void
    {
        $this->template->setVariable('allowedPrivilegeLevels', UserModel::ALLOWED_PRIVILEGE_LEVELS);
        $this->renderTemplate('user/create');
    }

    /**
     * Update a user.
     */
    public function update() : void
    {
        $this->validator->validate(__FUNCTION__);

        $targetUserId = $this->sanitize->int($this->getRouteParameters()['id']);
        $newPrivilegeLevel = $this->sanitize->int($_POST['privilegeLevel']);

        $this->userManagementService->updateUserPrivilegesForAdmin($this->getUserId(), $targetUserId, $newPrivilegeLevel);

        $this->updateView();
    }

    /**
     * Display a single user.
     */
    public function updateView() : void
    {
        $targetUserId = $this->sanitize->int($this->getRouteParameters()['id']);

        $user = $this->userManagementService->getUserForAdmin($this->getUserId(), $targetUserId);

        $this->template->setVariable('user', $user);
        $this->renderTemplate('user/update');
    }

    /**
     * Delete a user.
     */
    public function delete() : never
    {
        $targetUserId = $this->sanitize->int($this->getRouteParameters()['id']);

        // setting get variable for validator
        $_GET['form_key'] = $this->getRouteParameters()['antiCsrfToken'];

        /** @see UserValidator::delete() */
        $this->validator->validate(__FUNCTION__);

        $this->userManagementService->deleteUserForAdmin($this->getUserId(), $targetUserId);

        $this->setNotification(Notification::TYPE_SUCCESS, 'User was removed');

        $this->deleteView();
    }

    /**
     * Redirect user to all users page.
     */
    public function deleteView() : never
    {
        // This is in its own method for the convenience of the error handler.
        Redirect::to(self::USERS_URL);
    }
}
