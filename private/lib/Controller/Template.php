<?php

namespace App\Controller;

use App\Service\CategoryService;
use App\Service\TemplateService;
use App\Utility\Redirect;
use App\Utility\Sanitize;
use App\Validator\TemplateValidator;

class Template extends AbstractController
{
    public const TEMPLATES_URL = BASE_URL . '/templates';
    public const TEMPLATE_URL = BASE_URL . '/template';

    public const CREATE_TEMPLATE_URL = self::TEMPLATE_URL . '/create';
    public const CREATE_TEMPLATE_POST_URL = self::TEMPLATE_URL . '/create/action';
    public const VIEW_TEMPLATE_URL = self::TEMPLATE_URL . '/{id:\d+}';
    public const UPDATE_TEMPLATE_URL = self::VIEW_TEMPLATE_URL . '/update';
    public const UPDATE_TEMPLATE_POST_URL = self::VIEW_TEMPLATE_URL . '/update/action';
    public const DELETE_TEMPLATE_URL = self::VIEW_TEMPLATE_URL . '/delete/{antiCsrfToken}';

    private TemplateService $service;
    private TemplateValidator $validator;
    private CategoryService $categoryService;

    public function __construct(array $routeParameters)
    {
        parent::__construct($routeParameters);

        // for every action in this controller, the user must be logged in
        $this->redirectLoggedOutUsersToLoginPage();

        $this->service = new TemplateService();
        $this->validator = new TemplateValidator($_POST, $_GET);
        
        $this->categoryService = new CategoryService();
    }

    public function indexView(): void
    {
        $this->injectSessionVariableToTemplate();
        $templates = $this->service->getAllTemplatesForUser($this->getUserId());

        $this->template->setVariable('templates', $templates);
        $this->template->render('templates/all');
    }

    /**
     * Displays the create template form
     */
    public function createView(): void
    {
        $this->injectSessionVariableToTemplate();
        $categories = $this->categoryService->getAllCategoriesForUser($this->getUserId());

        $this->template->setVariable('categories', $categories);
        $this->template->render('templates/create');
    }

    /**
     * Save the data submitted from the create template form into the database
     */
    public function create(): void
    {
        /** @see TemplateValidator::create() */
        $this->validator->validate(__FUNCTION__);

        $categoryId      = Sanitize::int($_POST['category_id']);
        $templateTitle   = Sanitize::string($_POST['entry_title'], 'strip|capitalize');
        $templateContent = Sanitize::string($_POST['entry_content'], 'trim');

        $this->service->createTemplate(
            $this->getUserId(),
            $categoryId,
            $templateTitle,
            $templateContent,
        );

        Redirect::to(self::TEMPLATES_URL);
    }
}