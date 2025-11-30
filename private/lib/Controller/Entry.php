<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UserException;
use App\Service\AuthenticationService;
use App\Service\CategoryService;
use App\Service\EntryService;
use App\Service\TemplateService;
use App\Service\WidgetService;
use App\Utility\Notification;
use App\Utility\Redirect;
use App\Utility\Sanitize;
use App\Utility\Template;
use App\Validator\EntryValidator;
use DateTime;

final class Entry extends AbstractController
{
    public const string ENTRIES_URL = BASE_URL . '/entries';
    public const string ENTRY_URL = BASE_URL . '/entry';
    public const string CREATE_ENTRY_URL = self::ENTRY_URL . '/create';
    public const string VIEW_ENTRY_URL = self::ENTRY_URL . '/{id:\d+}';
    public const string UPDATE_ENTRY_URL = self::VIEW_ENTRY_URL . '/update';
    public const string DELETE_ENTRY_URL = self::VIEW_ENTRY_URL . '/delete/{antiCsrfToken}';
    public const string CREATE_ENTRY_POST_URL = self::CREATE_ENTRY_URL . '/action';
    public const string UPDATE_ENTRY_POST_URL = self::UPDATE_ENTRY_URL . '/action';

    private readonly EntryValidator $validator;

    public function __construct(
        AuthenticationService $authenticationService,
        Template $template,
        private readonly EntryService $service,
        private readonly CategoryService $categoryService,
        private readonly WidgetService $widgetService,
        private readonly TemplateService $templateService,
    ) {
        parent::__construct($authenticationService, $template);

        // for every action in this controller, the user must be logged in
        $this->redirectLoggedOutUsersToLoginPage();

        $this->validator = new EntryValidator($_POST, $_GET);
    }

    /**
     * Display all entries related to logged in user.
     */
    public function index() : void
    {
        $this->template->setVariable('get', $_GET);

        /** @see EntryValidator::index() */
        $this->validator->validate(__FUNCTION__);

        $searchQuery = Sanitize::getVariable($_GET, 'search_by_title', Sanitize::TYPE_STRING);
        $categoryId = Sanitize::getVariable($_GET, 'category_id', Sanitize::TYPE_INT);
        $createdDateFrom = Sanitize::getVariable($_GET, 'date_from', Sanitize::TYPE_STRING);
        $createdDateTo = Sanitize::getVariable($_GET, 'date_to', Sanitize::TYPE_STRING);
        $pageSize = Sanitize::getVariable($_GET, 'page_size', Sanitize::TYPE_INT) ?? 25;
        $page = Sanitize::getVariable($_GET, 'page', Sanitize::TYPE_INT) ?? 1;

        if ($createdDateFrom !== null) {
            $date = new DateTime($createdDateFrom);
            $date->setTime(0, 0, 0);
            $createdDateFrom = $date->getTimestamp();
        }

        if ($createdDateTo !== null) {
            $date = new DateTime($createdDateTo);
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
            $pageSize,
        );

        $this->template->setVariable(
            'enabledWidgets',
            $this->widgetService->getEnabledWidgetsForUser($this->getUserId()),
        );
        $this->template->setVariable('entries', $entries);
        $this->indexView();
    }

    public function indexView() : void
    {
        $categories = $this->categoryService->getAllCategoriesForUser($this->getUserId());

        $this->template->setVariable('categories', $categories);
        $this->renderTemplate('entry/all');
    }

    /**
     * Display a single entry.
     */
    public function entryView() : void
    {
        $entryId = Sanitize::int($this->getRouteParameters()['id']);

        try {
            $decorator = $this->service->getEntryForUser($entryId, $this->getUserId(), $this->getUserEncryptionKey());

            $this->template->setVariable('entry', $decorator);
        } catch (UserException $userException) {
            $this->template->setVariable(
                Notification::TYPE_ERROR,
                $userException->getMessage(),
            );
        }

        $this->renderTemplate('entry/view');
    }

    /**
     * Create an new entry.
     */
    public function create() : void
    {
        /** @see EntryValidator::create() */
        $this->validator->validate(__FUNCTION__);

        $categoryId = Sanitize::int($_POST['category_id']);
        $entryTitle = Sanitize::string($_POST['entry_title']);
        $entryContent = Sanitize::string($_POST['entry_content'], [Sanitize::OPTION_TRIM]);

        $entryId = $this->service->createEntry($this->getUserId(), $this->getUserEncryptionKey(), $categoryId, $entryTitle, $entryContent);

        $this->setNotification(Notification::TYPE_SUCCESS, sprintf('Entry %s has been created', $entryTitle));

        if (isset($_POST['redirectToEntriesOverview'])) {
            Redirect::to(self::ENTRIES_URL);
        }

        Redirect::to(self::ENTRY_URL . ('/' . $entryId));
    }

    /**
     * Display create a new entry form.
     */
    public function createView() : void
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
     * Update an entry.
     */
    public function update() : never
    {
        /** @see EntryValidator::update() */
        $this->validator->validate(__FUNCTION__);

        $entryId = Sanitize::int($this->getRouteParameters()['id']);
        $categoryId = Sanitize::int($_POST['category_id']);
        $entryTitle = Sanitize::string($_POST['entry_title']);
        $entryContent = Sanitize::string($_POST['entry_content'], [Sanitize::OPTION_TRIM]);

        $this->service->updateEntry(
            $this->getUserId(),
            $this->getUserEncryptionKey(),
            $entryId,
            $categoryId,
            $entryTitle,
            $entryContent,
        );

        $this->setNotification(
            Notification::TYPE_SUCCESS,
            sprintf('Entry %s has been updated', $entryTitle),
        );

        Redirect::to(self::ENTRY_URL . sprintf('/%d/', $entryId));
    }

    /**
     * Display an update entry form.
     */
    public function updateView() : void
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
        } catch (UserException $userException) {
            $this->template->setVariable(
                Notification::TYPE_ERROR,
                $userException->getMessage(),
            );
        }

        $this->renderTemplate('entry/update');
    }

    /**
     * Delete an existing entry.
     */
    public function delete() : never
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
     * Redirect user to all entries page.
     */
    public function deleteView() : never
    {
        // This is in its own method for the convenience of the error handler.
        Redirect::to(self::ENTRIES_URL);
    }
}
