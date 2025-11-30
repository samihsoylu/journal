<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Utility\Notification;
use App\Utility\Redirect;
use App\Utility\Sanitize;
use App\Utility\Session;
use App\Utility\Template;
use App\Utility\UserSession;
use App\Validator\AuthenticationValidator;

final class Authentication extends AbstractController
{
    // Route url constants, to keep paths consistent within multiple classes
    public const string LOGIN_URL = BASE_URL . '/login';
    public const string LOGIN_POST_URL = self::LOGIN_URL . '/action';
    public const string LOGOUT_URL = BASE_URL . '/logout';

    private readonly AuthenticationValidator $validator;

    public function __construct(
        private readonly AuthenticationService $authenticationService,
        Template $template,
    ) {
        parent::__construct($authenticationService, $template);

        $this->validator = new AuthenticationValidator($_POST);
    }

    /**
     * Login a user.
     */
    public function login() : void
    {
        $this->redirectLoggedInUsersToDashboard();

        /** @see AuthenticationValidator::login() */
        $this->validator->validate(__FUNCTION__);

        $username = Sanitize::string($_POST['username'], [Sanitize::OPTION_LOWERCASE, Sanitize::OPTION_STRIP]);
        $password = $_POST['password'];

        // Log the user in
        $this->authenticationService->login($username, $password);

        /** @see AbstractController::redirectLoggedOutUsersToLoginPage() */
        $referredFrom = Session::get('referred_from');

        if ($referredFrom !== null) {
            Session::delete('referred_from');

            Redirect::to(BASE_URL . ('/' . $referredFrom));
        }

        Redirect::to(Welcome::DASHBOARD_URL);
    }

    /**
     * Display a login form.
     */
    public function loginView() : void
    {
        $this->redirectLoggedInUsersToDashboard();

        $token = UserSession::generateNewAntiCSRFToken(uniqid());
        Session::put('login_form_key', $token);

        $this->template->setVariable('token', $token);

        $this->renderTemplate('authenticate/login');
    }

    /**
     * Logout a user.
     */
    public function logout() : never
    {
        $this->authenticationService->logout();

        $this->setNotification(Notification::TYPE_INFO, 'You have been logged out');

        Redirect::to(self::LOGIN_URL);
    }
}
