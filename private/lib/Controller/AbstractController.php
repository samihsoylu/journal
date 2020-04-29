<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Utilities\Redirect;
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
     * @var array route specific parameters
     */
    private array $routeParameters;

    protected const HOME_URL = BASE_URL . '/welcome';

    public function __construct(array $routeParameters)
    {
        // Instantiate blade templating engine
        $this->bladeInstance = new Blade([TEMPLATE_PATH],TEMPLATE_CACHE_PATH);

        // Router variables (/user/{userId})
        $this->routeParameters = $routeParameters;

        // Set default variables for all blade templates
        $this->bladeParameters = [
            'site_title' => $_ENV['SITE_TITLE'],
            'assets_url' => ASSETS_URL,
        ];
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

    protected function ensureUserIsLoggedIn(): void
    {
        $userIsLoggedIn = (new AuthenticationService())->isUserLoggedIn();

        // If the user is NOT logged in, then they must be redirected to the login page
        if (!$userIsLoggedIn) {
            Redirect::to(BASE_URL . '/');
        }
    }

    protected function ensureUserIsNotLoggedIn(): void
    {
        $userIsLoggedIn = (new AuthenticationService())->isUserLoggedIn();

        // If user IS logged in, then they likely must be redirected to the welcome page
        if ($userIsLoggedIn) {
            Redirect::to(self::HOME_URL);
        }
    }
}