<?php declare(strict_types=1);

namespace App\Controller;

use App\Exception\UserException;
use App\Service\CategoryService;
use App\Service\EntryService;
use App\Service\TemplateService;
use App\Service\WidgetService;
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
    private WidgetService $widgetService;
    private TemplateService $templateService;

    public function __construct(array $routeParameters)
    {
        parent::__construct($routeParameters);

        // for every action in this controller, the user must be logged in
        $this->redirectLoggedOutUsersToLoginPage();

        $this->service   = new EntryService();
        $this->validator = new EntryValidator($_POST, $_GET);

        $this->categoryService = new CategoryService();
        $this->widgetService = new WidgetService();
        $this->templateService = new TemplateService();
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

        $searchQuery     = Sanitize::getVariable($_GET, 'search_by_title', Sanitize::TYPE_STRING);
        $categoryId      = Sanitize::getVariable($_GET, 'category_id', Sanitize::TYPE_INT);
        $createdDateFrom = Sanitize::getVariable($_GET, 'date_from', Sanitize::TYPE_STRING);
        $createdDateTo   = Sanitize::getVariable($_GET, 'date_to', Sanitize::TYPE_STRING);
        $pageSize        = Sanitize::getVariable($_GET, 'page_size', Sanitize::TYPE_INT) ?? 25;
        $page            = Sanitize::getVariable($_GET, 'page', Sanitize::TYPE_INT) ?? 1;

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

        $this->template->setVariable(
            'enabledWidgets',
            $this->widgetService->getEnabledWidgetsForUser($this->getUserId())
        );
        $this->template->setVariable('entries', $entries);
        $this->indexView();
    }

    public function indexView(): void
    {
        $categories = $this->categoryService->getAllCategoriesForUser($this->getUserId());

        $this->template->setVariable('categories', $categories);
        $this->renderTemplate('entry/all');
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

        $this->renderTemplate('entry/view');
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
        $entryTitle   = Sanitize::string($_POST['entry_title']);
        $entryContent = Sanitize::string($_POST['entry_content'], [Sanitize::OPTION_TRIM]);

        $entryId = $this->service->createEntry($this->getUserId(), $this->getUserEncryptionKey(), $categoryId, $entryTitle, $entryContent);

        $this->setNotification(Notification::TYPE_SUCCESS, "Entry {$entryTitle} has been created");

        if (isset($_POST['redirectToEntriesOverview'])) {
            Redirect::to(self::ENTRIES_URL);
        }
        Redirect::to(self::ENTRY_URL . "/{$entryId}");
    }

    /**
     * Display create a new entry form
     *
     * @return void
     */
    public function createView(): void
    {
        $templates = $this->templateService->getAllTemplatesForUser($this->getUserId());
        $categories = $this->categoryService->getAllCategoriesForUser($this->getUserId());

        $this->template->setVariables([
            'templates' => $templates,
            'categories' => $categories,
        ]);

        $this->renderTemplate('entry/create');
    }

    /**
     * Update an entry
     *
     * @return void
     */
    public function update(): void
    {
        /** @see EntryValidator::update() */
        $this->validator->validate(__FUNCTION__);

        $entryId      = Sanitize::int($this->getRouteParameters()['id']);
        $categoryId   = Sanitize::int($_POST['category_id']);
        $entryTitle   = Sanitize::string($_POST['entry_title']);
        $entryContent = Sanitize::string($_POST['entry_content'], [Sanitize::OPTION_TRIM]);

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
            $templates = $this->templateService->getAllTemplatesForUser($this->getUserId());
            $categories = $this->categoryService->getAllCategoriesForUser($this->getUserId());
            $entryDecorator = $this->service->getEntryForUser($entryId, $this->getUserId(), $this->getUserEncryptionKey());

            $this->template->setVariables([
                'templates' => $templates,
                'entry' => $entryDecorator,
                'categories' => $categories,
            ]);
        } catch (UserException $e) {
            $this->template->setVariable(
                Notification::TYPE_ERROR,
                $e->getMessage()
            );
        }

        $this->renderTemplate('entry/update');
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
