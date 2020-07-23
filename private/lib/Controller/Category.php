<?php

namespace App\Controller;

use App\Exception\UserException;
use App\Service\CategoryService;
use App\Utility\Notification;
use App\Utility\Redirect;
use App\Validator\CategoryValidator;

class Category extends AbstractController
{
    // Route url constants, to keep paths consistent within multiple classes
    public const CATEGORIES_URL            = BASE_URL . '/categories';
    public const CREATE_CATEGORY_URL       = BASE_URL . '/category/create';

    public const UPDATE_CATEGORY_URL       = BASE_URL . '/category/update';
    public const UPDATE_CATEGORY_VIEW_URL  = BASE_URL . '/category/update/{id:\d+}';

    public const CREATE_CATEGORY_POST_URL  = BASE_URL . '/category/create/post';
    public const UPDATE_CATEGORY_POST_URL  = BASE_URL . '/category/update/{id:\d+}/post';

    protected CategoryService $service;

    protected CategoryValidator $validator;

    public function __construct(array $routeParameters)
    {
        parent::__construct($routeParameters);

        // for every action in this controller, the user must be logged in
        $this->ensureUserIsLoggedIn();

        $this->service   = new CategoryService();
        $this->validator = new CategoryValidator($_POST);
    }

    /**
     * Action for page that lists all categories
     *
     * @todo implement
     * @return void
     */
    public function index(): void
    {
//        //[$filterOne, $filterTwo, $etc] = $this->getRouteParameters();
        $entries = $this->service->getEntries();

        $this->template->setVariable('entries', $entries);

        $this->template->render('category/all');
    }

    /**
     * Action for page that shows a selected category
     *
     * @return void
     */
    public function read(): void
    {
        // Retrieves all categories for user from the database
        $categories = $this->service->getAllCategoriesForLoggedInUser();

        // Creates a variable in templating engine
        $this->template->setVariable('categories', $categories);

        // Renders the template
        $this->template->render('category/view-all');
    }

    /**
     * Action for page that sends a post request to create a category
     *
     * @return void
     */
    public function create(): void
    {
        try {
            /** @see CategoryValidator::create() */
            $this->validator->validate(__FUNCTION__);

            $title       = $_POST['category_name'];
            $description = $_POST['category_description'];

            // Create a new category
            $this->service->createCategory($title, $description);

            // Present success message
            $this->setNotification(
                Notification::TYPE_SUCCESS,
                "Category '{$title}' has been created"
            );
        } catch (UserException $e) {
            $this->setNotification(Notification::TYPE_ERROR, $e->getMessage());
        }

        $this->createView();
    }

    public function createView(): void
    {
        $this->template->render('category/create');
    }

    /**
     * Action for updating an existing category
     *
     * @return void
     */
    public function update(): void
    {
        try {
            /** @see CategoryValidator::update() */
            $this->validator->validate(__FUNCTION__);

            $id          = $this->getRouteParameters()['id'];
            $title       = $_POST['category_name'];
            $description = $_POST['category_description'];

            // Update category
            $this->service->updateCategory($id, $title, $description);

            // Present success message
            $this->setNotification(
                Notification::TYPE_SUCCESS,
                "Category '{$title}' was updated"
            );

            Redirect::to(self::CATEGORIES_URL);
        } catch (UserException $e) {
            $this->template->setVariable(Notification::TYPE_ERROR, $e->getMessage());
        }

        $this->updateView();
    }

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
     * Action for deleting an existing category
     *
     * @return void
     */
    public function delete(): void
    {
        // @todo delete a category
    }
}
