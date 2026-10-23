<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UserException\InvalidParameterException;
use App\Service\AuthenticationService;
use App\Service\Security\PendingLogin;
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
    public const string TWO_FACTOR_URL = BASE_URL . '/login/two-factor';
    public const string TWO_FACTOR_CANCEL_URL = self::TWO_FACTOR_URL . '/cancel';

    private readonly AuthenticationValidator $validator;

    public function __construct(
        private readonly AuthenticationService $authenticationService,
        Template $template,
        private readonly Sanitize $sanitize,
        private readonly PendingLogin $pending,
    ) {
        parent::__construct($authenticationService, $template);

        $this->validator = new AuthenticationValidator($_POST, [], $authenticationService->getUserSession());
    }

    /**
     * Login a user.
     */
    public function login() : void
    {
        $this->redirectLoggedInUsersToDashboard();

        /** @see AuthenticationValidator::login() */
        $this->validator->validate(__FUNCTION__);

        $username = $this->sanitize->string($_POST['username'], [Sanitize::OPTION_LOWERCASE, Sanitize::OPTION_STRIP]);
        $password = $_POST['password'];

        // Log the user in
        if ( ! $this->authenticationService->login($username, $password)) {
            Redirect::to(self::TWO_FACTOR_URL);
        }

        $this->redirectAfterLogin();
    }

    private function redirectAfterLogin() : never
    {

        /** @see AbstractController::redirectLoggedOutUsersToLoginPage() */
        $referredFrom = Session::get('referred_from');

        if ($referredFrom !== null) {
            Session::delete('referred_from');

            Redirect::to(Redirect::localDestination($referredFrom, Welcome::DASHBOARD_URL));
        }

        Redirect::to(Welcome::DASHBOARD_URL);
    }

    /**
     * Display a login form.
     */
    public function loginView() : void
    {
        $this->redirectLoggedInUsersToDashboard();

        if ($this->pending->state() !== null) {
            Redirect::to(self::TWO_FACTOR_URL);
        }

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
        $this->validator->validate(__FUNCTION__);
        $this->authenticationService->logout();

        $this->setNotification(Notification::TYPE_INFO, 'You have been logged out');

        Redirect::to(self::LOGIN_URL);
    }

    public function twoFactorView() : void
    {
        $this->redirectLoggedInUsersToDashboard();
        $state = $this->pending->state();

        if ($state === null) {
            Redirect::to(self::LOGIN_URL);
        }
        $this->template->setVariable('token', $state['csrf']);
        $this->renderTemplate('authenticate/two-factor');
    }

    public function twoFactor() : never
    {
        $this->redirectLoggedInUsersToDashboard();
        $code = $_POST['code'] ?? null;

        if ( ! is_string($code) || strlen($code) > 128 || $code === '') {
            throw InvalidParameterException::invalidFieldValue('code');
        }
        $this->authenticationService->completeTwoFactor($code, $_POST['form_key'] ?? null);
        $this->redirectAfterLogin();
    }

    public function cancelTwoFactor() : never
    {
        $this->pending->requireState($_POST['form_key'] ?? null);
        $this->pending->clear();
        Redirect::to(self::LOGIN_URL);
    }

    public function cancelTwoFactorView() : void
    {
        $this->twoFactorView();
    }

    public function logoutView() : never
    {
        Redirect::to(Welcome::DASHBOARD_URL);
    }
}
