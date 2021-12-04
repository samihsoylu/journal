<?php

namespace App\Controller;

class Error extends AbstractController
{
    public function __construct(array $routeParameters = [])
    {
        parent::__construct($routeParameters);
    }

    public function notFound(): void
    {
        $this->renderTemplate('errors/404');
    }

    public function methodNotAllowed(): void
    {
        $this->renderTemplate('errors/405');
    }
}
