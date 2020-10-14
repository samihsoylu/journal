<?php

namespace App\Controller;

use App\Exception\UserException;
use App\Service\CategoryService;
use App\Service\EntryService;
use App\Utility\Notification;
use App\Utility\Redirect;
use App\Utility\Sanitizer;
use App\Validator\EntryValidator;

class Entry extends AbstractController
{
    public const ENTRIES_URL      = BASE_URL . '/entries';
    public const ENTRY_URL        = BASE_URL . '/entry';

    public const CREATE_ENTRY_URL = self::ENTRY_URL . '/create';

    public const READ_ENTRY_URL   = self::ENTRY_URL . '/{id:\d+}';
    public const UPDATE_ENTRY_URL = self::READ_ENTRY_URL . '/update';
    public const DELETE_ENTRY_URL = self::READ_ENTRY_URL . '/delete';

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
     * View, displays all entries in the system associated with the logged in user
     * Url: /entries/
     *
     * @return void
     */
    public function index(): void
    {
        /** @see EntryValidator::index() */
        $this->validator->validate(__FUNCTION__);

        $searchQuery     = $_GET['search_by_title'] ?? null;
        $limit           = $_GET['entries_limit'] ?? null;
        $categoryId      = $_GET['category_id'] ?? null;
        $createdDateFrom = $_GET['date_from'] ?? null;
        $createdDateTo   = $_GET['date_to'] ?? null;

        if ($searchQuery !== null) {
            $searchQuery = Sanitizer::sanitizeString($searchQuery, 'strip');
        }
        if ($limit !== null) {
            $limit = Sanitizer::sanitizeString($limit, 'int');
        }
        if ($categoryId !== null) {
            $categoryId = Sanitizer::sanitizeString($categoryId, 'int');
        }

        if ($createdDateFrom !== null) {
            $createdDateFrom = strtotime($createdDateFrom);
        }
        if ($createdDateTo !== null) {
            $createdDateTo = strtotime($createdDateTo);
        }

        $entries = $this->entryService->getAllEntriesForUserFromFilter(
            $searchQuery,
            $limit,
            $categoryId,
            $createdDateFrom,
            $createdDateTo
        );
        if (count($entries) > 0) {
            $this->template->setVariable('entries', $entries);
        }

        $this->template->render('entry/all');
    }

    /**
     * View, displays a specific entry by the provided id in the url
     * Url: /entry/{id}
     *
     * @return void
     */
    public function read(): void
    {
        $entryId = $this->getRouteParameters()['id'];

        try {
            $entry = $this->entryService->findEntryById($entryId);

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
     * Post Action to create a new entry
     * Url: /entry/create/post
     *
     * @return void
     */
    public function create(): void
    {
        /** @see EntryValidator::create() */
        $this->validator->validate(__FUNCTION__);

        $categoryId   = $_POST['category_id'];
        $entryTitle   = Sanitizer::sanitizeString($_POST['entry_title'], 'strip|capitalize');
        $entryContent = Sanitizer::sanitizeString($_POST['entry_content'], 'htmlspecialchars');

        $entryId = $this->entryService->createEntry($categoryId, $entryTitle, $entryContent);

        $this->setNotification(Notification::TYPE_SUCCESS, "Entry {$entryTitle} has been created");

        Redirect::to(self::ENTRY_URL . "/{$entryId}");
    }

    /**
     * View, displays create an entry form
     * Url: /entry/create
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
     * Url: /entry/{id}/update/post
     *
     * @return void
     */
    public function update(): void
    {
        $this->template->setVariable('post', $_POST);

        /** @see EntryValidator::update() */
        $this->validator->validate(__FUNCTION__);

        $entryId      = $this->getRouteParameters()['id'];
        $categoryId   = $_POST['category_id'];
        $entryTitle   = Sanitizer::sanitizeString($_POST['entry_title'], 'strip|capitalize');
        $entryContent = Sanitizer::sanitizeString($_POST['entry_content'], 'htmlspecialchars');

        $this->entryService->updateEntry($entryId, $categoryId, $entryTitle, $entryContent);

        $this->setNotification(
            Notification::TYPE_SUCCESS,
            "Entry {$entryTitle} has been updated"
        );

        Redirect::to(self::ENTRY_URL . "/{$entryId}/");
    }

    /**
     * View for updating an existing entry
     * Url: /entry/{id}/update
     *
     * @return void
     */
    public function updateView(): void
    {
        $entryId = $this->getRouteParameters()['id'];

        try {
            $categories = $this->categoryService->getAllCategoriesForLoggedInUser();
            $entry = $this->entryService->findEntryById($entryId);

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
     * Action for deleting an existing entry
     *
     * @return void
     */
    public function delete(): void
    {
        $entryId = $this->getRouteParameters()['id'];

        $this->entryService->deleteEntry($entryId);

        $this->deleteView();
    }

    /**
     * Redirects user to entries page, this is in its own method because when an error is triggered in the delete()
     * method then the error handler will load this view method, which in turn will load the entries page and then
     * display a nice error.
     *
     * @return void
     */
    public function deleteView(): void
    {
        Redirect::to(self::ENTRIES_URL);
    }
}
