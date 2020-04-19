<?php declare(strict_types=1);

namespace App\Controller;

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

    protected function addToTemplateParameters(string $key, string $value): void
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
}