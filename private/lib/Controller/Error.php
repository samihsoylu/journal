<?php

namespace App\Controller;

class Error extends AbstractController
{
    public function __construct(array $routeParameters = [])
    {
        parent::__construct($routeParameters);
    }

    public function renderNotFoundPage(): void
    {
        $this->renderTemplate('errors/404');
        exit();
    }

    public function methodNotAllowed(): void
    {
        $this->renderTemplate('errors/405');
    }
}
