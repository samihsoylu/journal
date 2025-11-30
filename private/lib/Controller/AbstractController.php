<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Utility\Notification;
use App\Utility\Redirect;
use App\Utility\Session;
use App\Utility\Template;
use Defuse\Crypto\Key;
use Sentry\UserDataBag;

abstract class AbstractController
{
    /**
     * @var array route specific parameters (entryId, page, etc..)
     */
    private array $routeParameters = [];

    /**
     * @var Notification gives the ability to set notifications for inheriting controller classes
     */
    private readonly Notification $notification;

    public function __construct(
        private readonly AuthenticationService $authenticationService,
        /**
         * @var Template allows inheriting controller classes to render templates
         */
        protected Template $template,
    ) {
        $this->notification = new Notification();

        if (SENTRY_ENABLED) {
            \Sentry\configureScope(static function (\Sentry\State\Scope $scope) use ($authenticationService) : void {
                $data = UserDataBag::createFromUserIpAddress($_SERVER['REMOTE_ADDR']);
                $session = $authenticationService->getUserSession();

                if ($session instanceof \App\Utility\UserSession) {
                    $data->setId($session->getUserId());
                    $data->setUsername($session->getUsername());

                }
            });
        }
    }

    public function setRouteParameters(array $routeParameters) : void
    {
        $this->routeParameters = $routeParameters;
    }

    protected function getRouteParameters() : array
    {
        return $this->routeParameters;
    }

    protected function redirectLoggedOutUsersToLoginPage() : void
    {
        if ( ! $this->authenticationService->isUserLoggedIn()) {
            $this->setNotification(
                Notification::TYPE_ERROR,
                'You must login before you can access this page',
            );

            // keep track on which page the user attempted to load
            Session::put('referred_from', $_GET['url'] ?? Welcome::DASHBOARD_URL);

            Redirect::to(Authentication::LOGIN_URL);
        }
    }

    protected function redirectLoggedInUsersToDashboard() : void
    {
        if ($this->authenticationService->isUserLoggedIn()) {
            Redirect::to(Welcome::DASHBOARD_URL);
        }
    }

    /**
     * Give a 403 response if the logged in user does not have admin privileges.
     */
    protected function ensureUserHasAdminPrivileges() : void
    {
        if ($this->authenticationService->userHasAdminPrivileges() === false) {
            http_response_code(403);
            $this->template->render('errors/403');
            exit;
        }
    }

    /**
     * Sets a notification session to prevent data loss in-between redirects. Later used to display a notification
     * message to the user.
     *
     * @param string $notificationType error|info|success|warning
     */
    protected function setNotification(string $notificationType, string $notificationMessage) : void
    {
        $this->notification->set($notificationType, $notificationMessage);
    }

    /**
     * Get the user id of the logged in user.
     */
    protected function getUserId() : int
    {
        return $this->authenticationService->getUserId();
    }

    /**
     * Get the encryption key of the logged in user.
     */
    protected function getUserEncryptionKey() : Key
    {
        return $this->authenticationService->getUserDecodedEncryptionKey();
    }

    public function renderTemplate(string $templateName) : void
    {
        $this->template->setVariable('session', $this->authenticationService->getSessionDecorator());

        $this->template->render($templateName);
    }

    public function renderJsonResponse(array $response) : void
    {
        header('Content-Type: application/json');
        echo json_encode($response, JSON_THROW_ON_ERROR);
    }
}
