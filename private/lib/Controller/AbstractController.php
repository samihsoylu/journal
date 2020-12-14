<?php declare(strict_types=1);

namespace App\Controller;

use App\Database\Model\User;
use App\Service\AuthenticationService;
use App\Utility\Session;
use App\Utility\Template;
use App\Utility\Notification;
use App\Utility\Redirect;

abstract class AbstractController
{
    /**
     * @var array route specific parameters (entryId, page, etc..)
     */
    private array $routeParameters;

    /**
     * @var AuthenticationService used to determine user is logged in or not in inheriting controller classes
     */
    private AuthenticationService $authService;

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
        // Router variables (/user/{userId}/entry/{entryId}/)
        $this->routeParameters = $routeParameters;

        // Services that are needed for all classes that inherit abstract controller.
        $this->authService = new AuthenticationService();
        $this->template    = Template::getInstance();

        // User later to create notifications for the user
        $this->notification = new Notification();
    }

    protected function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    protected function redirectLoggedOutUsersToLoginPage(): void
    {
        if ($this->authService->userIsLoggedIn() === false) {
            $this->setNotification(
                Notification::TYPE_ERROR,
                'You must login before you can access this page'
            );

            /** @see Authentication::login() */
            // keep track on which page the user attempted to load
            Session::put('referred_from', $_GET['url']);

            Redirect::to(Authentication::LOGIN_URL);
        }
    }

    protected function redirectLoggedInUsersToDashboard(): void
    {
        if ($this->authService->userIsLoggedIn()) {
            Redirect::to(Welcome::DASHBOARD_URL);
        }
    }

    /**
     * Give a 403 response if the logged in user does not have admin privileges.
     *
     * @return void
     */
    protected function ensureUserHasAdminRights(): void
    {
        $userIsAdmin = $this->authService->userHasPrivilege(User::PRIVILEGE_LEVEL_ADMIN);
        $userIsOwner = $this->authService->userHasPrivilege(User::PRIVILEGE_LEVEL_OWNER);

        if (!$userIsAdmin && !$userIsOwner) {
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
}
