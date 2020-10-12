<?php

namespace App\Controller;

use App\Exception\UserException;
use App\Service\CategoryService;
use App\Utility\Notification;
use App\Utility\Redirect;
use App\Utility\Sanitizer;
use App\Validator\CategoryValidator;

class Category extends AbstractController
{
    // Route url constants, to keep paths consistent within multiple classes
    public const CATEGORIES_URL            = BASE_URL . '/categories';
    public const CATEGORY_URL              = BASE_URL . '/category';

    public const CREATE_CATEGORY_URL       = self::CATEGORY_URL . '/create';
    public const CREATE_CATEGORY_POST_URL  = self::CREATE_CATEGORY_URL . '/action';

    public const READ_CATEGORY_URL         = self::CATEGORY_URL . '/{id:\d+}';
    public const UPDATE_CATEGORY_URL       = self::READ_CATEGORY_URL . '/update';
    public const UPDATE_CATEGORY_POST_URL  = self::UPDATE_CATEGORY_URL . '/action';

    public const DELETE_CATEGORY_URL       = self::READ_CATEGORY_URL . '/delete';

    protected CategoryService $service;

    protected CategoryValidator $validator;

    public function __construct(array $routeParameters)
    {
        parent::__construct($routeParameters);

        // for every action in this controller, the user must be logged in
        $this->redirectLoggedOutUsersToLoginPage();

        $this->service   = new CategoryService();
        $this->validator = new CategoryValidator($_POST);
    }

    /**
     * View, displays categories related to the logged in user
     * Url: /categories/
     *
     * @return void
     */
    public function read(): void
    {
        $categories = $this->service->getAllCategoriesForLoggedInUser();

        $this->template->setVariable('categories', $categories);

        $this->template->render('category/view-all');
    }

    public function create(): void
    {
        /** @see CategoryValidator::create() */
        $this->validator->validate(__FUNCTION__);

        $title       = Sanitizer::sanitizeString($_POST['category_name'], 'strip|capitalize');
        $description = Sanitizer::sanitizeString($_POST['category_description'], 'htmlspecialchars');

        $this->service->createCategory($title, $description);

        $this->setNotification(
            Notification::TYPE_SUCCESS,
            "Category '{$title}' has been created"
        );

        Redirect::to(self::CATEGORIES_URL);
    }

    public function createView(): void
    {
        $this->template->render('category/create');
    }

    /**
     * Post action for updating an existing category
     * Url: /category/{id}/update/post
     *
     * @return void
     */
    public function update(): void
    {
        /** @see CategoryValidator::update() */
        $this->validator->validate(__FUNCTION__);

        $id          = $this->getRouteParameters()['id'];
        $title       = Sanitizer::sanitizeString($_POST['category_name'], 'strip|capitalize');
        $description = Sanitizer::sanitizeString($_POST['category_description'], 'htmlspecialchars');

        $this->service->updateCategory($id, $title, $description);

        $this->setNotification(
            Notification::TYPE_SUCCESS,
            "Category '{$title}' was updated"
        );

        Redirect::to(self::CATEGORIES_URL);
    }

    /**
     * View, displays update
     * Url: /category/{id}/update
     *
     * @return void
     */
    public function updateView(): void
    {
        $parameters = $this->getRouteParameters();

        try {
            // Find category
            $category = $this->service->getCategoryById($parameters['id']);

            // Creates a variable in templating engine
            $this->template->setVariable('category', $category);
        } catch (UserException $e) {
            $this->setNotification(Notification::TYPE_ERROR, $e->getMessage());
        }

        // Render template
        $this->template->render('category/update');
    }

    /**
     * Post action for deleting an existing category
     * Url: /category/{id}/delete
     *
     * @return void
     */
    public function delete(): void
    {
        $id = $this->getRouteParameters()['id'];

        $this->service->deleteCategoryAndAssociatedEntries($id);

        $this->setNotification(Notification::TYPE_SUCCESS, 'Category was removed');

        $this->deleteView();
    }

    public function deleteView(): void
    {
        Redirect::to(self::CATEGORIES_URL);
    }
}
