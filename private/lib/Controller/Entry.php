<?php declare(strict_types=1);

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

    private EntryService $service;
    private EntryValidator $validator;
    private CategoryService $categoryService;

    public function __construct(array $routeParameters)
    {
        parent::__construct($routeParameters);

        // for every action in this controller, the user must be logged in
        $this->redirectLoggedOutUsersToLoginPage();

        $this->service   = new EntryService();
        $this->validator = new EntryValidator($_POST, $_GET);

        $this->categoryService = new CategoryService();
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

        $entries = $this->service->getAllEntriesForUserFromFilter(
            $this->getUserId(),
            $searchQuery,
            $categoryId,
            $createdDateFrom,
            $createdDateTo,
            $page,
            $pageSize
        );

        $this->template->setVariable('entries', $entries);
        $this->indexView();
    }

    public function indexView(): void
    {
        $categories = $this->categoryService->getAllCategoriesForUser($this->getUserId());

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
        $entryId = Sanitize::int($this->getRouteParameters()['id']);

        try {
            $decorator = $this->service->getEntryForUser($entryId, $this->getUserId(), $this->getUserEncryptionKey());

            $this->template->setVariable('entry', $decorator);
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

        $entryId = $this->service->createEntry($this->getUserId(), $this->getUserEncryptionKey(), $categoryId, $entryTitle, $entryContent);

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
        $categories = $this->categoryService->getAllCategoriesForUser($this->getUserId());
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

        $entryId      = Sanitize::int($this->getRouteParameters()['id']);
        $categoryId   = Sanitize::int($_POST['category_id']);
        $entryTitle   = Sanitize::string($_POST['entry_title'], 'strip|capitalize');
        $entryContent = Sanitize::string($_POST['entry_content'], 'trim|htmlspecialchars');

        $this->service->updateEntry(
            $this->getUserId(),
            $this->getUserEncryptionKey(),
            $entryId,
            $categoryId,
            $entryTitle,
            $entryContent
        );

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
        $entryId = Sanitize::int($this->getRouteParameters()['id']);

        try {
            $categories = $this->categoryService->getAllCategoriesForUser($this->getUserId());
            $decorator = $this->service->getEntryForUser($entryId, $this->getUserId(), $this->getUserEncryptionKey());

            $this->template->setVariables([
                'entry' => $decorator,
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
        $entryId = Sanitize::int($this->getRouteParameters()['id']);

        // setting get variable for validator
        $_GET['form_key'] = $this->getRouteParameters()['antiCsrfToken'];

        /** @see EntryValidator::delete() */
        $this->validator->validate(__FUNCTION__);

        $this->service->deleteEntry($entryId, $this->getUserId());

        $this->setNotification(Notification::TYPE_SUCCESS, 'Entry was removed');

        $this->deleteView();
    }

    /**
     * Redirect user to all entries page
     *
     * @return void
     */
    public function deleteView(): void
    {
        // This is in its own method for the convenience of the error handler.
        Redirect::to(self::ENTRIES_URL);
    }
}
