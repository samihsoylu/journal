<?php

namespace App\Controller;

use App\Exception\UserException;
use App\Service\CategoryService;
use App\Service\EntryService;
use App\Utility\Notification;
use App\Utility\Redirect;
use App\Utility\Sanitize;
use App\Validator\EntryValidator;

class Entry extends AbstractController
{
    public const ENTRIES_URL      = BASE_URL . '/entries';
    public const ENTRY_URL        = BASE_URL . '/entry';

    public const CREATE_ENTRY_URL = self::ENTRY_URL . '/create';

    public const VIEW_ENTRY_URL   = self::ENTRY_URL . '/{id:\d+}';
    public const UPDATE_ENTRY_URL = self::VIEW_ENTRY_URL . '/update';
    public const DELETE_ENTRY_URL = self::VIEW_ENTRY_URL . '/delete/{antiCsrfToken}';

    public const CREATE_ENTRY_POST_URL = self::CREATE_ENTRY_URL . '/action';
    public const UPDATE_ENTRY_POST_URL = self::UPDATE_ENTRY_URL . '/action';

    protected EntryService $entryService;

    protected CategoryService $categoryService;

    protected EntryValidator $validator;

    public function __construct(array $routeParameters)
    {
        parent::__construct($routeParameters);

        // for every action in this controller, the user must be logged in
        $this->redirectLoggedOutUsersToLoginPage();

        $this->entryService = new EntryService();
        $this->categoryService = new CategoryService();
        $this->validator = new EntryValidator($_POST, $_GET);
    }

    /**
     * Display all entries related to logged in user
     *
     * @return void
     */
    public function index(): void
    {
        $this->template->setVariable('get', $_GET);

        /** @see EntryValidator::index() */
        $this->validator->validate(__FUNCTION__);

        $searchQuery     = Sanitize::getVariable($_GET, 'search_by_title', 'string', 'trim|htmlspecialchars');
        $categoryId      = Sanitize::getVariable($_GET, 'category_id', 'int');
        $createdDateFrom = Sanitize::getVariable($_GET, 'date_from', 'string', 'trim|htmlspecialchars');
        $createdDateTo   = Sanitize::getVariable($_GET, 'date_to', 'string', 'trim|htmlspecialchars');
        $pageSize        = Sanitize::getVariable($_GET, 'page_size', 'int') ?? 25;
        $page            = Sanitize::getVariable($_GET, 'page', 'int') ?? 1;

        if ($createdDateFrom !== null) {
            $date = new \DateTime($createdDateFrom);
            $date->setTime(0, 0, 0);
            $createdDateFrom = $date->getTimestamp();
        }
        if ($createdDateTo !== null) {
            $date = new \DateTime($createdDateTo);
            $date->setTime(23, 59, 59);
            $createdDateTo = $date->getTimestamp();
        }

        [$currentPage, $totalPages, $entries] = $this->entryService->getAllEntriesForUserFromFilter(
            $searchQuery,
            $categoryId,
            $createdDateFrom,
            $createdDateTo,
            $page,
            $pageSize
        );

        $this->template->setVariables([
            'entries'     => $entries,
            'totalPages'  => $totalPages,
            'currentPage' => $currentPage,
            'filterUrl'   => $this->entryService->getHelper()->getUriForPageFilter($page),
        ]);

        $this->indexView();
    }

    public function indexView(): void
    {
        $categories = $this->categoryService->getAllCategoriesForLoggedInUser();

        $this->template->setVariable('categories', $categories);
        $this->template->render('entry/all');
    }

    /**
     * Display a single entry
     *
     * @return void
     */
    public function entryView(): void
    {
        $entryId = $this->getRouteParameters()['id'];

        try {
            $entry = $this->entryService->getEntryById($entryId);

            $this->template->setVariable('entry', $entry);
        } catch (UserException $e) {
            $this->template->setVariable(
                Notification::TYPE_ERROR,
                $e->getMessage()
            );
        }

        $this->template->render('entry/view');
    }

    /**
     * Create an new entry
     *
     * @return void
     */
    public function create(): void
    {
        /** @see EntryValidator::create() */
        $this->validator->validate(__FUNCTION__);

        $categoryId   = Sanitize::int($_POST['category_id']);
        $entryTitle   = Sanitize::string($_POST['entry_title'], 'strip|capitalize');
        $entryContent = Sanitize::string($_POST['entry_content'], 'trim|htmlspecialchars');

        $entryId = $this->entryService->createEntry($categoryId, $entryTitle, $entryContent);

        $this->setNotification(Notification::TYPE_SUCCESS, "Entry {$entryTitle} has been created");

        Redirect::to(self::ENTRY_URL . "/{$entryId}");
    }

    /**
     * Display create a new entry form
     *
     * @return void
     */
    public function createView(): void
    {
        $categories = $this->categoryService->getAllCategoriesForLoggedInUser();
        $this->template->setVariable('categories', $categories);

        $this->template->render('entry/create');
    }

    /**
     * Update an entry
     *
     * @return void
     */
    public function update(): void
    {
        $this->template->setVariable('post', $_POST);

        /** @see EntryValidator::update() */
        $this->validator->validate(__FUNCTION__);

        $entryId      = $this->getRouteParameters()['id'];
        $categoryId   = Sanitize::int($_POST['category_id']);
        $entryTitle   = Sanitize::string($_POST['entry_title'], 'strip|capitalize');
        $entryContent = Sanitize::string($_POST['entry_content'], 'trim|htmlspecialchars');

        $this->entryService->updateEntry($entryId, $categoryId, $entryTitle, $entryContent);

        $this->setNotification(
            Notification::TYPE_SUCCESS,
            "Entry {$entryTitle} has been updated"
        );

        Redirect::to(self::ENTRY_URL . "/{$entryId}/");
    }

    /**
     * Display an update entry form
     *
     * @return void
     */
    public function updateView(): void
    {
        $entryId = $this->getRouteParameters()['id'];

        try {
            $categories = $this->categoryService->getAllCategoriesForLoggedInUser();
            $entry = $this->entryService->getEntryById($entryId);

            $this->template->setVariables([
                'entry' => $entry,
                'categories' => $categories,
            ]);
        } catch (UserException $e) {
            $this->template->setVariable(
                Notification::TYPE_ERROR,
                $e->getMessage()
            );
        }

        $this->template->render('entry/update');
    }

    /**
     * Delete an existing entry
     *
     * @return void
     */
    public function delete(): void
    {
        $entryId = $this->getRouteParameters()['id'];

        // setting get variable for validator
        $_GET['form_key'] = $this->getRouteParameters()['antiCsrfToken'];

        $this->entryService->deleteEntry($entryId);

        $this->setNotification(Notification::TYPE_SUCCESS, 'Entry was removed');

        $this->deleteView();
    }

    /**
     * Redirect user to all entries page
     *
     * @note This is in its own method for the convenience of the error handler.
     * @return void
     */
    public function deleteView(): void
    {
        Redirect::to(self::ENTRIES_URL);
    }
}
