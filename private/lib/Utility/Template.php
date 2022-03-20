<?php

namespace App\Utility;

use Jenssegers\Blade\Blade;

/**
 * Class Template a utility for rendering blade templates
 */
class Template
{
    /**
     * @var null|self look up singleton design pattern
     */
    protected static $instance = null;

    /**
     * @var Blade templating library
     */
    protected Blade $blade;

    /**
     * @var Notification used to show notification to the user
     */
    protected Notification $notification;

    /**
     * @var array list of variables that will be passed on to the template
     */
    protected array $variables;

    private function __construct()
    {
        $this->blade = new Blade([TEMPLATE_PATH], TEMPLATE_CACHE_PATH);
        $this->notification = new Notification();

        $this->setAllUrlConstantsToVariables();
        $this->setDefaultVariables();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Set multiple variables that will be passed on to the blade templating engine
     *
     * @param array $variables
     */
    public function setVariables(array $variables): void
    {
        foreach ($variables as $key => $value) {
            $this->setVariable($key, $value);
        }
    }

    /**
     * Set a variable name that will be passed on to the blade templating engine
     *
     * @param string $key variable name
     * @param mixed $value
     */
    public function setVariable(string $key, $value): void
    {
        $this->variables[$key] = $value;
    }

    /**
     * Renders a blade template
     *
     * @param string $templateName
     */
    public function render(string $templateName): void
    {
        // Check if notifications were set prior to attempting to load the blade template
        if ($this->notification->exists()) {
            [$type, $message] = $this->notification->get();

            // Adds new user notification to variable list.
            $this->setVariable($type, $message);
        }

        if (count($_POST) > 0) {
            // This condition is likely true when there is a validation error on the website.
            // Form is submitted -> UserException is caught -> then the same form page is rendered with a error notification
            $this->setVariable('post', $_POST);
        }

        echo $this->blade->render($templateName, $this->variables);
    }

    /**
     * Dynamically loads existing url constants in all controllers into the templating engine.
     *
     * @return void
     */
    private function setAllUrlConstantsToVariables(): void
    {
        $controllers = array_diff(scandir(BASE_PATH . '/private/lib/Controller'), array('..', '.'));

        $variableList = [];
        foreach ($controllers as $controller) {
            if (strpos($controller, 'Abstract') !== false) {
                // ignore abstract class
                continue;
            }
            $controllerName = str_replace('.php', '', $controller);

            $refl = new \ReflectionClass("\\App\\Controller\\{$controllerName}");
            foreach ($refl->getConstants() as $key => $value) {
                if (strpos($key, '_URL') === false) {
                    // if constant does not have url in its name, ignore it.
                    continue;
                }

                $variableList[strtolower($key)] = $value;
            }
        }

        $this->setVariables($variableList);
    }

    /**
     * Finds current user's active page. Used later to determine which navigation item should be set to active.
     *
     * @return string
     */
    private function getActivePage(): string
    {
        // Remove base url
        $activePage = str_replace(BASE_URL, '', $_SERVER['REQUEST_URI']);

        // Remove sub pages
        $activePage = explode('/', $activePage)[1];

        // Remove get request parameters
        $activePage = explode('?', $activePage)[0];

        return $activePage;
    }

    /**
     * Sets default variables required to render all templates
     *
     * @return void
     */
    private function setDefaultVariables(): void
    {
        $struct = [
            'site_title'  => SITE_TITLE,
            'base_url'    => BASE_URL,
            'assets_url'  => ASSETS_URL,
            'active_page' => $this->getActivePage(),
        ];

        $this->setVariables($struct);
    }

    /**
     * Clears template cache directory so that changes go in to effect immediately
     *
     * @return void
     */
    public function clearCache(): void
    {
        $staticTemplateFiles = glob(TEMPLATE_CACHE_PATH . '/*.php');
        foreach ($staticTemplateFiles as $staticFile) {
            unlink($staticFile);
        }
    }
}
