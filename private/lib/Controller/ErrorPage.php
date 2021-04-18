<?php

namespace App\Controller;

class ErrorPage extends AbstractController
{
    public function __construct(array $routeParameters)
    {
        parent::__construct($routeParameters);
    }

    public function notFound(): void
    {
        $this->template->render('errors/404');
    }

    public function methodNotAllowed(): void
    {
        $this->template->render('errors/405');
    }
}