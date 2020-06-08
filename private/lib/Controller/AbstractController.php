<?php declare(strict_types=1);

namespace App\Controller;

use App\Database\Model\User;
use App\Service\AuthenticationService;
use App\Service\NotificationService;
use App\Utility\Redirect;
use App\Utility\Session;
use Doctrine\Common\NotifyPropertyChanged;
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

    private AuthenticationService $authenticationService;
    private NotificationService $notificationService;

    public function __construct(array $routeParameters)
    {
        // Instantiate blade templating engine
        $this->bladeInstance = new Blade([TEMPLATE_PATH],TEMPLATE_CACHE_PATH);

        // Router variables (/user/{userId}/entry/{entryId}/)
        $this->routeParameters = $routeParameters;

        // Instantiates auth service
        $this->authenticationService = new AuthenticationService();

        // Set default variables for all blade templates
        $this->bladeParameters = [
            'site_title' => $_ENV['SITE_TITLE'],
            'assets_url' => ASSETS_URL,
            'logout_url' => Authentication::LOGOUT_URL,
        ];

        $this->notificationService = new NotificationService();
    }

    protected function getBladeInstance(): Blade
    {
        return $this->bladeInstance;
    }

    protected function addToBladeParameters(string $key, string $value): void
    {
        $this->bladeParameters[$key] = $value;
    }

    protected function getBladeParameters(): array
    {
        return $this->bladeParameters;
    }

    protected function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    protected function getNotificationService(): NotificationService
    {
        return $this->notificationService;
    }

    /**
     * Used for users who are not logged in, in most cases they should be redirected to the login page because they must
     * be logged in to see the web page.
     *
     * @return void
     */
    protected function ensureUserIsLoggedIn(): void
    {
        $userIsLoggedIn = $this->authenticationService->isUserLoggedIn();

        if (!$userIsLoggedIn) {
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
        $userIsLoggedIn = $this->authenticationService->isUserLoggedIn();

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
        $userIsAdmin = $this->authenticationService->userHasPrivilege(User::PRIVILEGE_LEVEL_ADMIN);

        if (!$userIsAdmin) {
            http_response_code(403);
            $this->render('errors/403');
            exit();
        }
    }

    /**
     * Before rendering a template that includes the 'alerts' blade template, run a check with this method in your
     * controller to ensure that you are providing notifications to the user
     *
     * @return void
     */
    protected function checkForNotificationMessages(): void
    {
        if ($this->getNotificationService()->isHit()) {
            [$notifyType, $notifyMessage] = $this->getNotificationService()->getNotification();

            $this->addToBladeParameters($notifyType, $notifyMessage);
        }
    }

    protected function isPostRequest(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] === 'POST');
    }

    /**
     * Shortening the default render method, now you no longer need to provide blade parameters, this is automatic.
     *
     * @param string $templatePath
     */
    protected function render(string $templatePath): void
    {
        echo $this->getBladeInstance()->render($templatePath, $this->getBladeParameters());
    }
}