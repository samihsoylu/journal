<?php

namespace App\Controller;

class Entry extends AbstractController
{
    public function __construct(array $routeParameters)
    {
        parent::__construct($routeParameters);

        // for every action in this controller, the user must be logged in
        $this->ensureUserIsLoggedIn();
    }

    public function index(): void
    {
        // display all entries?
        // what about categories?
    }
}