<?php declare(strict_types=1);

namespace App\Controller;

use App\Database\Model\User;
use App\Service\AuthenticationService;
use App\Utility\Notification;
use App\Utility\Redirect;
use Jenssegers\Blade\Blade;

abstract class AbstractController
{
    /**
     * @var Blade template object
     */
    private Blade $bladeInstance;

    /**
     * @var array template specific parameters
     */
    private array $bladeParameters;

    /**
     * @var array route specific parameters (entryId, page, etc..)
     */
    private array $routeParameters;

    private AuthenticationService $authService;

    private Notification $notificationUtil;

    public function __construct(array $routeParameters)
    {
        // Instantiate blade templating engine
        $this->bladeInstance = new Blade([TEMPLATE_PATH],TEMPLATE_CACHE_PATH);

        // Router variables (/user/{userId}/entry/{entryId}/)
        $this->routeParameters = $routeParameters;

        // Instantiates auth service
        $this->authService = new AuthenticationService();

        // Set default variables for all blade templates
        $this->bladeParameters = [
            'site_title' => $_ENV['SITE_TITLE'],
            'assets_url' => ASSETS_URL,
            'logout_url' => Authentication::LOGOUT_URL,
        ];

        $this->notificationUtil = new Notification();
    }

    protected function addToBladeParameters(string $key, string $value): void
    {
        $this->bladeParameters[$key] = $value;
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
            Redirect::to(Welcome::HOME_URL);
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
            $this->render('errors/403');
            exit();
        }
    }

    /**
     * Checks whether a post request was made when a page is loaded. Returns true if a form is submitted.
     *
     * @return bool
     */
    protected function requestIsPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] === 'POST');
    }

    /**
     * Shortens the default render method by eliminating the variable parameter, and additionally checks for notifications.
     *
     * @param string $templatePath
     */
    protected function render(string $templatePath): void
    {
        // Ensures that success/error/info/warning message shows after page loads
        if ($this->notificationUtil->exists()) {
            [$notifyType, $notifyMessage] = $this->notificationUtil->get();

            $this->addToBladeParameters($notifyType, $notifyMessage);
        }

        echo $this->bladeInstance->render($templatePath, $this->bladeParameters);
    }

    protected function setNotification(string $notificationType, string $notificationMessage): void
    {
        $this->notificationUtil->set($notificationType, $notificationMessage);
    }
}