<?php

namespace App\Controller;

use App\Service\EntryService;

class Entry extends AbstractController
{
    public const ENTRIES_URL = BASE_URL . '/entries';
    public const ENTRY_URL   = BASE_URL . '/entry';

    protected $service;

    public function __construct(array $routeParameters)
    {
        parent::__construct($routeParameters);

        // for every action in this controller, the user must be logged in
        $this->ensureUserIsLoggedIn();

        $this->service = new EntryService();
    }

    public function index(): void
    {
        //[$filterOne, $filterTwo, $etc] = $this->getRouteParameters();
        $entries = $this->service->getEntries();
        if ($entries !== null) {
            $this->template->setVariable('entries', $entries);
        }

        $this->template->render('entries');
    }
}
