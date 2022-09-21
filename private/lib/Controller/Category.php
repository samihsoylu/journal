<?php declare(strict_types=1);

namespace App\Controller;

use App\Exception\UserException;
use App\Utility\Notification;
use App\Utility\Redirect;
use App\Utility\Sanitize;
use App\Validator\CategoryValidator;
use App\Service\CategoryService;
use App\Database\Model\Category as CategoryModel;

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

    public const DELETE_CATEGORY_URL       = self::READ_CATEGORY_URL . '/delete/{antiCsrfToken}';

    public const SET_CATEGORY_ORDER_URL    = self::CATEGORIES_URL . '/ajax/sort-order';
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
     * Display all categories except <uncategorized> category that belong to the logged in user
     *
     * @return void
     */
    public function indexView(): void
    {
        $categories = $this->service->getAllCategoriesWithExcludeFilter($this->getUserId(), [CategoryModel::UNCATEGORIZED_CATEGORY_NAME]);

        $this->template->setVariable('categories', $categories);
        $this->renderTemplate('category/all');
    }

    /**
     * Create a new category
     *
     * @return void
     */
    public function create(): void
    {
        /** @see CategoryValidator::create() */
        $this->validator->validate(__FUNCTION__);

        $title       = Sanitize::string($_POST['category_name']);
        $description = Sanitize::string($_POST['category_description']);

        $this->service->createCategory($this->getUserId(), $title, $description);

        $this->setNotification(
            Notification::TYPE_SUCCESS,
            "Category '{$title}' has been created"
        );

        Redirect::to(self::CATEGORIES_URL);
    }

    /**
     * Display a create category form
     *
     * @return void
     */
    public function createView(): void
    {
        $this->renderTemplate('category/create');
    }

    /**
     * Update an existing category
     *
     * @return void
     */
    public function update(): void
    {
        /** @see CategoryValidator::update() */
        $this->validator->validate(__FUNCTION__);

        $categoryId  = Sanitize::int($this->getRouteParameters()['id']);
        $title       = Sanitize::string($_POST['category_name']);
        $description = Sanitize::string($_POST['category_description']);

        $this->service->updateCategory($this->getUserId(), $categoryId, $title, $description);

        $this->setNotification(
            Notification::TYPE_SUCCESS,
            "Category '{$title}' was updated"
        );

        Redirect::to(self::CATEGORIES_URL);
    }

    /**
     * Display an update category form
     *
     * @return void
     */
    public function updateView(): void
    {
        $categoryId = Sanitize::int($this->getRouteParameters()['id']);

        try {
            $category = $this->service->getCategoryForUser($categoryId, $this->getUserId());

            $this->template->setVariable('category', $category);
        } catch (UserException $e) {
            $this->setNotification(Notification::TYPE_ERROR, $e->getMessage());
        }

        $this->renderTemplate('category/update');
    }

    /**
     * Delete a category
     *
     * @return void
     */
    public function delete(): void
    {
        $categoryId = Sanitize::int($this->getRouteParameters()['id']);

        // setting get variable for validator
        $_GET['form_key'] = $this->getRouteParameters()['antiCsrfToken'];

        /** @see CategoryValidator::delete() */
        $this->validator->validate(__FUNCTION__);

        $this->service->deleteCategory($this->getUserId(), $categoryId);

        $this->setNotification(Notification::TYPE_SUCCESS, 'Category was removed');

        $this->deleteView();
    }

    /**
     * Redirect the user to all categories page
     *
     * @return void
     */
    public function deleteView(): void
    {
        // This is in its own method for the convenience of the error handler.
        Redirect::to(self::CATEGORIES_URL);
    }

    public function setCategoryOrder(): void
    {
        /** @see CategoryValidator::setCategoryOrder() */
        $this->validator->validate(__FUNCTION__);

        $sortOrders = $_POST['orderedCategoryIds'];

        try {
            /**
             * Received from ajax post request
             * array (
             *     // SORT ORDER => ID
             *     0 => 5,
             *     1 => 6,
             *     2 => 3,
             *     3 => 4,
             *     4 => 1,
             *     5 => 2,
             * )
             */
            foreach ($sortOrders as $sortOrder => $categoryId) {
                $categoryId = Sanitize::int($categoryId);

                $this->service->updateCategoryOrder($this->getUserId(), $categoryId, $sortOrder);
            }
        } catch (UserException $e) {
            http_response_code(404);
            echo $e->getMessage();
        }
    }
}
