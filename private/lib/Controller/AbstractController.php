<?php declare(strict_types=1);

namespace App\Controller;

use App\Database\Model\User;
use App\Service\AuthenticationService;
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

        // Services relevant to all inheriting controllers
        $this->authService = new AuthenticationService();
        $this->template    = Template::getInstance();

        // User later to create notifications for the user
        $this->notification    = new Notification();

        // Set default variables for all templates
        $this->template->setVariables([
            'site_title'  => $_ENV['SITE_TITLE'],
            'assets_url'  => ASSETS_URL,
            'active_page' => $this->getActivePage(),
        ]);
    }

    protected function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    /**
     * Used for users who are not logged in, in most cases they should be redirected to the login page because they must
     * be logged in to see every web page.
     *
     * @return void
     */
    protected function ensureUserIsLoggedIn(): void
    {
        $userIsLoggedIn = $this->authService->isUserLoggedIn();

        if (!$userIsLoggedIn) {
            $this->setNotification(Notification::TYPE_ERROR, 'You must login before you can access this page');
            Redirect::to(Authentication::LOGIN_URL);
        }
    }

    /**
     * Used for users who are already logged in, in most cases they should be redirected to the welcome page because
     * they should not be able to access registration or login pages.
     *
     * @return void
     */
    protected function ensureUserIsNotLoggedIn(): void
    {
        $userIsLoggedIn = $this->authService->isUserLoggedIn();

        if ($userIsLoggedIn) {
            Redirect::to(Welcome::DASHBOARD_URL);
        }
    }

    /**
     * Checks if the current user has a specific privilege, works well for admin specific pages. Renders a 403 page if
     * the current user does not meet specified privilege level.
     *
     * @return void
     */
    protected function ensureUserHasAdminRights(): void
    {
        $userIsAdmin = $this->authService->userHasPrivilege(User::PRIVILEGE_LEVEL_ADMIN);

        if (!$userIsAdmin) {
            http_response_code(403);
            $this->template->render('errors/403');
            exit();
        }
    }

    /**
     * Checks whether a post request was made when a page is loaded. Returns true if a form is submitted.
     *
     * @return bool
     */
    protected function isPostRequest(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] === 'POST');
    }

    /**
     * Finds current users active page
     *
     * @return string
     */
    protected function getActivePage(): string
    {
        // Remove base url
        $activePage = str_replace(BASE_URL, '', $_SERVER['REQUEST_URI']);

        // Remove sub pages
        $activePage = explode('/', $activePage)[1];

        // Remove get request
        $activePage = explode('?', $activePage)[0];

        return $activePage;
    }

    /**
     * Sets a notification session to prevent data loss in-between redirects.
     *
     * @param string $notificationType error|info|success|warning
     * @param string $notificationMessage
     */
    protected function setNotification(string $notificationType, string $notificationMessage): void
    {
        $this->notification->set($notificationType, $notificationMessage);
    }
}
