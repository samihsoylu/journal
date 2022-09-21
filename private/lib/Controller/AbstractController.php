<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Utility\Registry;
use App\Utility\Session;
use App\Utility\Template;
use App\Utility\Notification;
use App\Utility\Redirect;
use Defuse\Crypto\Key;
use Sentry\UserDataBag;

abstract class AbstractController
{
    /**
     * @var array route specific parameters (entryId, page, etc..)
     */
    private array $routeParameters;

    /**
     * @var AuthenticationService used to determine user is logged in or not in inheriting controller classes
     */
    private AuthenticationService $authenticationService;

    /**
     * @var Notification gives the ability to set notifications for inheriting controller classes
     */
    private Notification $notification;

    /**
     * @var Template allows inheriting controller classes to render templates
     */
    protected Template $template;

    public function __construct(array $routeParameters)
    {
        /** @var AuthenticationService $authenticationService */
        $authenticationService = Registry::get(AuthenticationService::class);
        $this->authenticationService = $authenticationService;

        // Router variables (/user/{userId}/entry/{entryId}/)
        $this->routeParameters = $routeParameters;
        $this->template        = Template::getInstance();
        $this->notification    = new Notification();

        if (SENTRY_ENABLED) {
            \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($authenticationService): void {
                $data = UserDataBag::createFromUserIpAddress($_SERVER['REMOTE_ADDR']);
                $session = $authenticationService->getUserSession();
                if ($session !== null) {
                    $data->setId($session->getUserId());
                    $data->setUsername($session->getUsername());
                    ;
                }
            });
        }
    }

    protected function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    protected function redirectLoggedOutUsersToLoginPage(): void
    {
        if (!$this->authenticationService->isUserLoggedIn()) {
            $this->setNotification(
                Notification::TYPE_ERROR,
                'You must login before you can access this page'
            );

            // keep track on which page the user attempted to load
            Session::put('referred_from', $_GET['url'] ?? Welcome::DASHBOARD_URL);

            Redirect::to(Authentication::LOGIN_URL);
        }
    }

    protected function redirectLoggedInUsersToDashboard(): void
    {
        if ($this->authenticationService->isUserLoggedIn()) {
            Redirect::to(Welcome::DASHBOARD_URL);
        }
    }

    /**
     * Give a 403 response if the logged in user does not have admin privileges.
     *
     * @return void
     */
    protected function ensureUserHasAdminPrivileges(): void
    {
        if ($this->authenticationService->userHasAdminPrivileges() === false) {
            http_response_code(403);
            $this->template->render('errors/403');
            exit();
        }
    }

    /**
     * Sets a notification session to prevent data loss in-between redirects. Later used to display a notification
     * message to the user.
     *
     * @param string $notificationType error|info|success|warning
     * @param string $notificationMessage
     */
    protected function setNotification(string $notificationType, string $notificationMessage): void
    {
        $this->notification->set($notificationType, $notificationMessage);
    }

    /**
     * Get the user id of the logged in user
     *
     * @return int
     */
    protected function getUserId(): int
    {
        return $this->authenticationService->getUserId();
    }

    /**
     * Get the encryption key of the logged in user
     *
     * @return Key
     */
    protected function getUserEncryptionKey(): Key
    {
        return $this->authenticationService->getUserDecodedEncryptionKey();
    }

    public function renderTemplate(string $templateName): void
    {
        $this->template->setVariable('session', $this->authenticationService->getSessionDecorator());

        $this->template->render($templateName);
    }

    public function renderJsonResponse(array $response): void
    {
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
