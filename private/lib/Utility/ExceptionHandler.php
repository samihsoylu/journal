<?php declare(strict_types=1);

namespace App\Utility;

use Throwable;

use function Sentry\captureException;

class ExceptionHandler
{
    private Throwable $exception;
    private Template $templatingEngine;
    private Notification $notifier;

    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
        $this->templatingEngine = Template::getInstance();
        $this->notifier = new Notification();
    }

    public function getException(): Throwable
    {
        return $this->exception;
    }

    private function getTemplatingEngine(): Template
    {
        return $this->templatingEngine;
    }

    /**
     * Sets a notification that is stored in a cookie in between redirects. It is rendered on the first page the user
     * visits.
     *
     * @see \App\Utility\Template::render()
     * @param string $message
     * @return void
     */
    private function setNotification(string $message): void
    {
        $this->notifier->set(Notification::TYPE_ERROR, $message);
    }

    /**
     * User related errors are handled by this method. This method makes use of the rule that an action must have a view
     * to display a nice error message to the user. This is why every action has an implemented "View" method that
     * provides this.
     *
     * To explain the rule, as an example, if a function login() exists in the Authentication controller, it is also
     * expected to have a loginView() method implemented. The loginView is then rendered below with a notification set.
     *
     * @param object $controller
     * @param string $methodName
     * @return void
     *
     * @throws Throwable
     */
    public function userException(object $controller, string $methodName): void
    {
        $this->setNotification($this->getException()->getMessage());

        if (!str_contains($methodName, 'View')) {
            // Ensures action template is renamed to view, example: login() changes to loginView()
            $methodName .= 'View';
        }

        try {
            // Render a template
            $controller->{$methodName}();
        } catch (\Exception $e) {
            $this->genericException();
        }
    }

    public function genericException(): void
    {
        if (DEBUG_MODE) {
            throw $this->getException();
        }

        // Send exception to Sentry for tracking and monitoring
        // Sentry provides stack traces, user context, request data, and notifications
        if (SENTRY_ENABLED) {
            captureException($this->getException());
        }

        http_response_code(500);
        $this->getTemplatingEngine()->render('errors/500');
    }
}
