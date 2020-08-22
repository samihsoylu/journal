<?php

namespace App\Controller;

use App\Exception\UserException;
use App\Service\CategoryService;
use App\Service\EntryService;
use App\Utility\Notification;
use App\Utility\Redirect;
use App\Validator\EntryValidator;

class Entry extends AbstractController
{
    public const ENTRIES_URL      = BASE_URL . '/entries';
    public const ENTRY_URL        = BASE_URL . '/entry';
    public const CREATE_ENTRY_URL = BASE_URL . '/entry/create';
    public const UPDATE_ENTRY_URL = BASE_URL . '/entry/update';
    public const DELETE_ENTRY_URL = BASE_URL . '/entry/delete';

    public const UPDATE_ENTRY_VIEW_URL = BASE_URL . '/entry/update/{id:\d+}';

    public const CREATE_ENTRY_POST_URL = BASE_URL . '/entry/create/post';
    public const UPDATE_ENTRY_POST_URL = BASE_URL . '/entry/update/{id:\d+}/post';
    public const DELETE_ENTRY_POST_URL = BASE_URL . '/entry/delete/{id:\d+}';

    protected EntryService $entryService;

    protected CategoryService $categoryService;

    protected EntryValidator $validator;

    public function __construct(array $routeParameters)
    {
        parent::__construct($routeParameters);

        // for every action in this controller, the user must be logged in
        $this->ensureUserIsLoggedIn();

        $this->entryService = new EntryService();
        $this->categoryService = new CategoryService();
        $this->validator = new EntryValidator($_POST);
    }

    /**
     * Action for page that lists all entries
     *
     * @return void
     */
    public function index(): void
    {
        //[$filterOne, $filterTwo, $etc] = $this->getRouteParameters();
        $entries = $this->entryService->getAllEntriesForUser();
        if (count($entries) > 0) {
            $this->template->setVariable('entries', $entries);
        }

        $this->template->render('entry/all');
    }

    /**
     * Action for page that shows a selected entry
     *
     * @return void
     */
    public function read(): void
    {
        $this->template->render('entry/view');
    }

    /**
     * Action for page that makes a post request to create an Entry.
     *
     * @return void
     */
    public function create(): void
    {
        try {
            /** @see EntryValidator::create() */
            $this->validator->validate(__FUNCTION__);

            $categoryId = $_POST['category_id'];
            $title      = $_POST['entry_title'];
            $content    = $_POST['entry_content'];

            // Create a new entry
            $this->entryService->createEntry($categoryId, $title, $content);

            // Present success message
            $this->setNotification(Notification::TYPE_SUCCESS, "Entry {$title} has been created");

            Redirect::to(self::ENTRIES_URL);
        } catch (UserException $e) {
            $this->template->setVariable('post', $_POST);
            $this->userExceptionHandler($e->getMessage());
        }

        $this->createView();
    }

    public function createView(): void
    {
        $categories = $this->categoryService->getAllCategoriesForLoggedInUser();
        $this->template->setVariable('categories', $categories);

        $this->template->render('entry/create');
    }

    /**
     * Action for updating an existing entry
     *
     * @return void
     */
    public function update(): void
    {
        $this->template->render('entry/update');
    }

    /**
     * Action for deleting an existing entry
     *
     * @return void
     */
    public function delete(): void
    {
        // @todo remove an entry
    }
}
