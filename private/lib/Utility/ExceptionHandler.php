<?php

namespace App\Utility;

class ExceptionHandler
{
    public static function genericException(\Exception $e): void
    {
        mail('mail@samihsoylu.nl', 'Exception in Journal', print_r($e, true));

        if (DEBUG_MODE) {
            throw $e;
        }
    }

    /**
     * User related errors are handled by this method. This method makes use of the rule that an action must have a view
     * to display a nice error message to the user. This is why every action has an implemented "View" method that
     * provides this.
     *
     * To explain the rule, as an example, if a function login() exists in the Authentication controller, it is also
     * expected to have a loginView() method implemented.
     *
     * @param string $exceptionMessage
     * @param object $controller
     * @param string $methodName
     * @return void
     *
     * @throws \Exception
     */
    public static function userException(string $exceptionMessage, object $controller, string $methodName): void
    {
        // this is how we pass variables to templates in between redirects, the template rendering class takes care of
        // considering the set notification.
        $notification = new Notification();
        $notification->set(Notification::TYPE_ERROR, $exceptionMessage);

        if (count($_POST) > 0) {
            // in some cases, the view can expect a post variable to display submitted values in a previous attempt.
            $template = Template::getInstance();
            $template->setVariable('post', $_POST);
        }

        if (strpos($methodName, 'View') === false) {
            // Ensures action template is renamed to view, example: login() changes to loginView()
            $methodName .= 'View';
        }

        if (method_exists($controller, $methodName)) {
            try {
                // Render a template
                $controller->{$methodName}();
            } catch (\Exception $e) {
                self::genericException($e);
            }
        }
    }
}